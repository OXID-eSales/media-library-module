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
use OxidEsales\MediaLibrary\Image\Service\ImageResourceInterface;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactoredInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailResourceInterface;
use OxidEsales\MediaLibrary\Media\DataType\Media as MediaDataType;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
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
        protected ImageResourceRefactoredInterface $imageResourceRefactored,
        protected ThumbnailResourceInterface $thumbnailResource,
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

    public function renameNew(string $mediaId, string $newMediaName): MediaInterface
    {
        $currentMedia = $this->mediaRepository->getMediaById($mediaId);

        // TODO: Encapsulate this in ImageResource service?
        $uniqueFileName = $this->namingService->getUniqueFilename(
            Path::join(
                $this->imageResourceRefactored->getPathToMediaFiles($currentMedia->getFolderName()),
                $this->namingService->sanitizeFilename($newMediaName)
            ),
        );
        $sanitizedName = basename($uniqueFileName);

        // TODO: Move this to ThumbnailService?
        $this->fileSystemService->deleteByGlob(
            inPath: $this->thumbnailResource->getPathToThumbnailFiles($currentMedia->getFolderName()),
            globTargetToDelete: $this->thumbnailResource->getThumbnailsGlob($currentMedia->getFileName())
        );

        $this->fileSystemService->rename(
            $this->imageResourceRefactored->getPathToMediaFile($currentMedia),
            Path::join(
                $this->imageResourceRefactored->getPathToMediaFiles($currentMedia->getFolderName()),
                $sanitizedName
            )
        );

        return $this->mediaRepository->renameMedia($mediaId, $sanitizedName);
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

    public function delete(array $ids): void
    {
        foreach ($ids as $oneId) {
            $mediaItem = $this->mediaRepository->getMediaById($oneId);
            $this->deleteMedia($mediaItem);
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

    public function deleteMedia(MediaInterface $media): void
    {
        $this->fileSystemService->delete($this->imageResourceRefactored->getPathToMediaFile($media));

        // TODO: Move this to ThumbnailService?
        $this->fileSystemService->deleteByGlob(
            inPath: $this->thumbnailResource->getPathToThumbnailFiles($media->getFolderName()),
            globTargetToDelete: $this->thumbnailResource->getThumbnailsGlob($media->getFileName())
        );

        $this->mediaRepository->deleteMedia($media->getOxid());
    }
}
