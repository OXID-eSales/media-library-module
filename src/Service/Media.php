<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Service;

use Doctrine\DBAL\Connection;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceInterface;
use OxidEsales\MediaLibrary\Media\DataType\Media as MediaDataType;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Transput\RequestData\UIRequestInterface;
use Symfony\Component\Filesystem\Path;
use Webmozart\Glob\Glob;

class Media
{
    protected Connection $connection;

    public function __construct(
        protected Config $shopConfig,
        ConnectionProviderInterface $connectionProvider,
        public ImageResourceInterface $imageResource,
        protected NamingServiceInterface $namingService,
        protected MediaRepositoryInterface $mediaRepository,
        private FileSystemServiceInterface $fileSystemService,
        protected ShopAdapterInterface $shopAdapter,
        protected UIRequestInterface $UIRequest,
    ) {
        $this->connection = $connectionProvider->get();
    }


    /**
     * todo: exception in place of bool response
     */


    public function uploadMedia($sSourcePath, $sDestPath, $sFileSize, $sFileType)
    {
        $this->createDirs();

        $aResult = [];
        if ($this->namingService->validateFileName(basename($sDestPath))) {
            $sDestPath = $this->namingService->getUniqueFilename($sDestPath);
            $finalFileName = basename($sDestPath);

            $this->moveUploadedFile($sSourcePath, $sDestPath);

            $newMediaId = $this->shopAdapter->generateUniqueId();

            $imageSize = $this->fileSystemService->getImageSize($sDestPath);

            $newMedia = new MediaDataType(
                oxid: $newMediaId,
                fileName: $finalFileName,
                fileSize: (int)$sFileSize,
                fileType: $sFileType,
                imageSize: $imageSize,
                folderId: $this->UIRequest->getFolderId()
            );

            $this->mediaRepository->addMedia($newMedia);

            $aResult['id'] = $newMediaId;
            $aResult['filename'] = $finalFileName;
            $aResult['thumb'] = $this->imageResource->getThumbnailUrl($finalFileName);
            $aResult['imagesize'] = $imageSize->getInFormat('%dx%d', '');
        }

        return $aResult;
    }

    public function createDirs()
    {
        $this->fileSystemService->ensureDirectory($this->imageResource->getMediaPath());
        $this->fileSystemService->ensureDirectory($this->imageResource->getThumbnailPath());
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

            $this->mediaRepository->renameMedia($sId, $sNewName);

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

                        $this->fileSystemService->ensureDirectory($sNewThumbPath);

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
     * @param              $sSourcePath
     * @param array|string $sDestPath
     *
     * @return bool
     */
    protected function moveUploadedFile($sSourcePath, array|string $sDestPath): bool
    {
        return move_uploaded_file($sSourcePath, $sDestPath);
    }
}
