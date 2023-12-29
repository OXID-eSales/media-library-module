<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Service;

use Doctrine\DBAL\Connection;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailGeneratorInterface;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceInterface;
use OxidEsales\MediaLibrary\Media\DataType\Media as MediaDataType;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use Symfony\Component\Filesystem\Path;
use Webmozart\Glob\Glob;

class Media
{
    public const AMOUNT_OF_FILES = "18";

    protected Connection $connection;
    protected $_aFileExtBlacklist = [
        'php.*',
        'exe',
        'js',
        'jsp',
        'cgi',
        'cmf',
        'phtml',
        'pht',
        'phar',
    ]; // regex allowed


    public function __construct(
        protected ModuleSettings $moduleSettings,
        protected Config $shopConfig,
        ConnectionProviderInterface $connectionProvider,
        protected UtilsObject $utilsObject,
        public ThumbnailGeneratorInterface $thumbnailGenerator,
        public ImageResourceInterface $imageResource,
        protected NamingServiceInterface $namingService,
        protected MediaRepositoryInterface $mediaRepository
    ) {
        $this->connection = $connectionProvider->get();
    }


    /**
     * todo: exception in place of bool response
     */


    public function uploadMedia($sSourcePath, $sDestPath, $sFileSize, $sFileType, $blCreateThumbs = false)
    {
        $this->createDirs();

        $sThumbName = '';

        $sDestPath = $this->namingService->getUniqueFilename($sDestPath);

        $sFileName = basename($sDestPath);
        $iFileCount = 0;

        $aResult = [];
        if ($this->validateFilename($sFileName)) {
            $this->moveUploadedFile($sSourcePath, $sDestPath);

            if ($blCreateThumbs) {
                try {
                    $sThumbName = $this->imageResource->createThumbnail($sFileName);
                } catch (\Exception $e) {
                    $sThumbName = '';
                }
            }

            $aFile = [
                'filename' => $sFileName,
                'thumbnail' => $sThumbName,
            ];

            $sId = $this->generateUId();
            $sThumbName = $aFile['thumbnail'];
            $sFileName = $aFile['filename'];

            $imageSize = new ImageSize(0, 0);
            if (is_readable($sDestPath) && preg_match("/image\//", $sFileType)) {
                $aImageSize = $this->getImageSize($sDestPath);
                $imageSize = new ImageSize($aImageSize[0] ?? 0, $aImageSize[1] ?? 0);
            }

            $newMedia = new MediaDataType(
                oxid: $sId,
                fileName: $sFileName,
                fileSize: (int)$sFileSize,
                fileType: $sFileType,
                thumbFileName: $sThumbName,
                imageSize: $imageSize,
                folderId: $this->imageResource->getFolderId()
            );

            $this->mediaRepository->addMedia($newMedia);

            $aResult['id'] = $sId;
            $aResult['filename'] = $sFileName;
            $aResult['thumb'] = $this->imageResource->getThumbnailUrl($sFileName);
            $aResult['imagesize'] = $imageSize->getInFormat('%dx%d', '');
        }

        return $aResult;
    }


    public function validateFilename($sFileName)
    {
        $aFileNameParts = explode('.', $sFileName);
        $aFileNameParts = array_reverse($aFileNameParts);

        $sFileNameExt = $aFileNameParts[0];

        foreach ($this->_aFileExtBlacklist as $sBlacklistPattern) {
            if (preg_match("/" . $sBlacklistPattern . "/", $sFileNameExt)) {
                throw new \Exception(Registry::getLang()->translateString('DD_MEDIA_EXCEPTION_INVALID_FILEEXT'));
            }
        }

        return true;
    }

    public function createDirs()
    {
        if (!is_dir($this->imageResource->getMediaPath())) {
            mkdir($this->imageResource->getMediaPath());
        }

        if (!is_dir($this->imageResource->getThumbnailPath())) {
            mkdir($this->imageResource->getThumbnailPath());
        }
    }

    public function createCustomDir($sName)
    {
        $this->createDirs();

        $sPath = $this->imageResource->getMediaPath();
        $sNewPath = $sPath . $sName;

        $sNewPath = $this->namingService->getUniqueFilename($sNewPath);

        if (!is_dir($sNewPath)) {
            mkdir($sNewPath);
        }

        // todo: before adding entry into db the existence should be checked

        $sFolderName = basename($sNewPath);

        $sSelect = "SELECT OXID FROM `ddmedia` WHERE `DDFILENAME` = ? AND `DDFILETYPE` = ?";
        $sId = $this->connection->fetchOne($sSelect, [$sFolderName, 'directory']);

        if (!$sId) {
            $sId = $this->generateUId();

            $newMedia = new MediaDataType(
                oxid: $sId,
                fileName: $sFolderName,
                fileType: 'directory'
            );

            $this->mediaRepository->addMedia($newMedia);
        }


        return ['id' => $sId, 'dir' => $sFolderName];
    }

    public function rename($sOldName, $sNewName, $sId, $sType = 'file')
    {
        $aResult = [
            'success'  => false,
            'filename' => '',
        ];

        // sanitize filename
        $sNewName = $this->namingService->sanitizeFilename($sNewName);

        $sPath = $this->imageResource->getMediaPath();

        $sOldPath = $sPath . $sOldName;
        $sNewPath = $sPath . $sNewName;

        $blDirectory = $sType == 'directory';
        $sNewPath = $this->namingService->getUniqueFilename($sNewPath);

        $sOldThumbHash = $sNewThumbHash = $sNewThumbName = '';
        if (!$blDirectory) {
            $thumbSize = sprintf(
                '%1$d*%1$d',
                $this->imageResource->getDefaultThumbnailSize()
            );
            $sOldThumbName = $this->imageResource->getThumbName(basename($sOldPath));
            $sOldThumbHash = str_replace('_thumb_' . $thumbSize . '.jpg', '', $sOldThumbName);
            $sNewThumbName = $this->imageResource->getThumbName(basename($sNewPath));
            $sNewThumbHash = str_replace('_thumb_' . $thumbSize . '.jpg', '', $sNewThumbName);
        }

        if (rename($sOldPath, $sNewPath)) {
            if (!$blDirectory) {
                $thumbs = Glob::glob(
                    Path::join(
                        $this->imageResource->getMediaPath(),
                        'thumbs',
                        $sOldThumbHash . '*'
                    )
                );
                foreach ($thumbs as $sThumb) {
                    $sNewName = str_replace($sOldThumbHash, $sNewThumbHash, $sThumb);
                    rename($sThumb, $sNewName);
                }
            }

            $sNewName = basename($sNewPath);
            $iShopId = $this->shopConfig->getActiveShop()->getShopId();

            $sUpdate = "UPDATE `ddmedia`
                              SET `DDFILENAME` = ?, `DDTHUMB` = ? 
                            WHERE `OXID` = ? AND `OXSHOPID` = ?;";

            $this->connection->executeQuery(
                $sUpdate,
                [
                    $sNewName,
                    !$blDirectory ? basename($sNewThumbName) : '',
                    $sId,
                    $iShopId,
                ]
            );

            $aResult = [
                'success'  => true,
                'filename' => $sNewName,
            ];
        }

        return $aResult;
    }

    public function moveFileToFolder($sSourceFileID, $sTargetFolderID)
    {
        $blReturn = false;

        if ($sTargetFolderID) {
            $sSelect = "SELECT DDFILENAME FROM ddmedia WHERE OXID = ?";
            $sTargetFolderName = $this->connection->fetchOne($sSelect, [$sTargetFolderID]);

            $sSourceFileName = $sThumb = '';
            $sSelect = "SELECT DDFILENAME, DDTHUMB FROM ddmedia WHERE OXID = ?";
            $aData = $this->connection->fetchAllAssociative($sSelect, [$sSourceFileID]);
            if (count($aData)) {
                $sSourceFileName = $aData[0]['DDFILENAME'];
                $sThumb = $aData[0]['DDTHUMB'];
            }

            if ($sTargetFolderName && $sSourceFileName) {
                $sOldName = $this->imageResource->getMediaPath() . $sSourceFileName;
                $sNewName = $this->imageResource->getMediaPath() . $sTargetFolderName . '/' . $sSourceFileName;

                if (rename($sOldName, $sNewName)) {
                    if ($sThumb) {
                        $sOldThumbPath = $this->imageResource->getMediaPath() . 'thumbs/';
                        $sNewThumbPath = $this->imageResource->getMediaPath() . $sTargetFolderName . '/thumbs/';

                        if (!is_dir($sNewThumbPath)) {
                            mkdir($sNewThumbPath);
                        }

                        foreach (
                            Glob::glob(
                                $sOldThumbPath . str_replace(
                                    sprintf(
                                        'thumb_%1$d*%1$d.jpg',
                                        $this->imageResource->getDefaultThumbnailSize()
                                    ),
                                    '*',
                                    $sThumb
                                )
                            ) as $sThumbFile
                        ) {
                            rename($sThumbFile, $sNewThumbPath . basename($sThumbFile));
                        }
                    }

                    $iShopId = $this->shopConfig->getActiveShop()->getShopId();

                    $sUpdate = "UPDATE `ddmedia`
                                      SET `DDFOLDERID` = ?  
                                    WHERE `OXID` = ? AND `OXSHOPID` = ?;";

                    $this->connection->executeQuery(
                        $sUpdate,
                        [
                            $sTargetFolderID,
                            $sSourceFileID,
                            $iShopId,
                        ]
                    );

                    $blReturn = true;
                }
            }
        }

        return $blReturn;
    }

    /**
     * @param $sNewName
     *
     * @return mixed|null|string|string[]
     */
    public function delete($aIds)
    {
        foreach ($aIds as $iKey => $sId) {
            $aIds[$iKey] = $this->connection->quote($sId);
        }
        $sIds = implode(",", $aIds);

        $sSelect = "SELECT `OXID`, `DDFILENAME`, `DDTHUMB`, `DDFILETYPE`, `DDFOLDERID` 
                FROM `ddmedia` WHERE `OXID` IN($sIds) OR `DDFOLDERID` IN($sIds) ORDER BY `DDFOLDERID` ASC;";
        $aData = $this->connection->fetchAllAssociative($sSelect);

        $aFolders = [];
        foreach ($aData as $sKey => $aRow) {
            if ($aRow['DDFILETYPE'] == 'directory') {
                $aFolders[$aRow['OXID']] = $aRow['DDFILENAME'];
                unset($aData[$sKey]);
            }
        }

        foreach ($aData as $aRow) {
            if ($aRow['DDFILETYPE'] != 'directory') {
                $sFolderName = '';
                if ($aRow['DDFOLDERID'] && isset($aFolders[$aRow['DDFOLDERID']])) {
                    $sFolderName = $aFolders[$aRow['DDFOLDERID']];
                }
                unlink(Path::join($this->imageResource->getMediaPath(), $sFolderName, $aRow['DDFILENAME']));

                if ($aRow['DDTHUMB']) {
                    $thumbFilename = sprintf(
                        'thumb_%1$d*%1$d.jpg',
                        $this->imageResource->getDefaultThumbnailSize()
                    );
                    $thumbs = Glob::glob(
                        Path::join(
                            $this->imageResource->getMediaPath(),
                            $sFolderName,
                            'thumbs',
                            str_replace($thumbFilename, '*', $aRow['DDTHUMB'])
                        )
                    );
                    foreach ($thumbs as $sThumb) {
                        unlink($sThumb);
                    }
                }

                $sDelete = "DELETE FROM `ddmedia` WHERE `OXID` = '" . $aRow['OXID'] . "'; ";
                $this->connection->executeQuery($sDelete);
            }
        }

        // remove folder
        foreach ($aFolders as $sOxid => $sFolderName) {
            @rmdir(Path::join($this->imageResource->getMediaPath(), $sFolderName, 'thumbs'));
            @rmdir(Path::join($this->imageResource->getMediaPath(), $sFolderName));
            $sDelete = "DELETE FROM `ddmedia` WHERE `OXID` = '" . $sOxid . "'; ";
            $this->connection->executeQuery($sDelete);
        }
    }

    /**
     * @return string
     */
    protected function generateUId(): string
    {
        return $this->utilsObject->generateUId();
    }

    /**
     * @param              $sSourcePath
     * @param array|string $sDestPath
     *
     * @return bool
     */
    protected function moveUploadedFile($sSourcePath, array|string $sDestPath): bool
    {
        return move_uploaded_file($sSourcePath, $sDestPath);
    }

    /**
     * @param array|string $sDestPath
     *
     * @return array|false
     */
    protected function getImageSize(array|string $sDestPath): array|false
    {
        return getimagesize($sDestPath);
    }
}
