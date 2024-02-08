<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Service;

use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceInterface;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactoredInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailResourceInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailServiceInterface;
use OxidEsales\MediaLibrary\Media\DataType\Media as MediaDataType;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Transput\RequestData\UIRequestInterface;
use Symfony\Component\Filesystem\Path;

class Media
{
    public function __construct(
        public ImageResourceInterface $imageResource,
        protected NamingServiceInterface $namingService,
        protected MediaRepositoryInterface $mediaRepository,
        private FileSystemServiceInterface $fileSystemService,
        protected ShopAdapterInterface $shopAdapter,
        protected UIRequestInterface $UIRequest,
        protected ImageResourceRefactoredInterface $imageResourceRefactored,
        protected ThumbnailServiceInterface $thumbnailService,
    ) {
    }

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

    public function rename(string $mediaId, string $newMediaName): MediaInterface
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

        $this->thumbnailService->deleteMediaThumbnails($currentMedia);

        $this->fileSystemService->rename(
            $this->imageResourceRefactored->getPathToMediaFile($currentMedia),
            Path::join(
                $this->imageResourceRefactored->getPathToMediaFiles($currentMedia->getFolderName()),
                $sanitizedName
            )
        );

        return $this->mediaRepository->renameMedia($mediaId, $sanitizedName);
    }

    public function moveToFolder(string $mediaId, string $folderId): void
    {
        $media = $this->mediaRepository->getMediaById($mediaId);
        $folder = $this->mediaRepository->getMediaById($folderId);

        $this->thumbnailService->deleteMediaThumbnails($media);

        // TODO: Encapsulate this in ImageResource service?
        $uniqueFileName = $this->namingService->getUniqueFilename(
            Path::join(
                $this->imageResourceRefactored->getPathToMediaFiles($folder->getFileName()),
                $media->getFileName()
            ),
        );
        $newUniqueName = basename($uniqueFileName);

        if ($newUniqueName !== $media->getFileName()) {
            $this->mediaRepository->renameMedia($mediaId, $newUniqueName);
        }

        $this->fileSystemService->rename(
            $this->imageResourceRefactored->getPathToMediaFile($media),
            $uniqueFileName
        );

        $this->mediaRepository->changeMediaFolderId($mediaId, $folderId);
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
        $this->thumbnailService->deleteMediaThumbnails($media);
        $this->mediaRepository->deleteMedia($media->getOxid());
    }
}
