<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Service;

use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceInterface;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactoredInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailServiceInterface;
use OxidEsales\MediaLibrary\Media\DataType\Media as MediaDataType;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Service\FileSystemServiceInterface;
use OxidEsales\MediaLibrary\Service\NamingServiceInterface;
use OxidEsales\MediaLibrary\Transput\RequestData\UIRequestInterface;

class MediaService implements MediaServiceInterface
{
    public function __construct(
        public ImageResourceInterface $imageResource,
        protected NamingServiceInterface $namingService,
        protected MediaRepositoryInterface $mediaRepository,
        private FileSystemServiceInterface $fileSystemService,
        protected ImageResourceRefactoredInterface $imageResourceRefactored,
        protected ThumbnailServiceInterface $thumbnailService,
    ) {
    }

    public function upload(string $uploadedFilePath, string $folderId, string $fileName): MediaInterface
    {
        $newMediaId = $this->namingService->getUniqueId();
        $folderName = '';

        if ($folderId) {
            $folder = $this->mediaRepository->getMediaById($folderId);
            $folderName = $folder->getFileName();
        }

        $newMediaPath = $this->imageResourceRefactored->getPossibleMediaFilePath($folderName, $fileName);

        $this->fileSystemService->moveUploadedFile($uploadedFilePath, $newMediaPath->getPath());

        $newMedia = new MediaDataType(
            oxid: $newMediaId,
            fileName: $newMediaPath->getFileName(),
            fileSize: $this->fileSystemService->getFileSize($newMediaPath->getPath()),
            fileType: $this->fileSystemService->getMimeType($newMediaPath->getPath()),
            imageSize: $this->fileSystemService->getImageSize($newMediaPath->getPath()),
            folderId: $folderId,
        );

        $this->mediaRepository->addMedia($newMedia);

        return $this->mediaRepository->getMediaById($newMediaId);
    }

    public function createDirs()
    {
        $this->fileSystemService->ensureDirectory($this->imageResource->getMediaPath());
        $this->fileSystemService->ensureDirectory($this->imageResource->getThumbnailPath());
    }

    public function rename(string $mediaId, string $newMediaName): MediaInterface
    {
        $currentMedia = $this->mediaRepository->getMediaById($mediaId);

        // todo: move sanitize up, as it does not belong here
        $uniqueFileName = $this->imageResourceRefactored->getPossibleMediaFilePath(
            folderName: $currentMedia->getFolderName(),
            fileName: $this->namingService->sanitizeFilename($newMediaName)
        );

        $this->thumbnailService->deleteMediaThumbnails($currentMedia);

        $this->fileSystemService->rename(
            $this->imageResourceRefactored->getPathToMediaFile($currentMedia),
            $uniqueFileName->getPath()
        );

        return $this->mediaRepository->renameMedia($mediaId, $uniqueFileName->getFileName());
    }

    public function moveToFolder(string $mediaId, string $folderId): void
    {
        $media = $this->mediaRepository->getMediaById($mediaId);
        $folder = $this->mediaRepository->getMediaById($folderId);

        $this->thumbnailService->deleteMediaThumbnails($media);

        $uniqueFileName = $this->imageResourceRefactored->getPossibleMediaFilePath(
            folderName: $folder->getFileName(),
            fileName: $media->getFileName()
        );

        if ($uniqueFileName->getFileName() !== $media->getFileName()) {
            $this->mediaRepository->renameMedia($mediaId, $uniqueFileName->getFileName());
        }

        $this->fileSystemService->rename(
            $this->imageResourceRefactored->getPathToMediaFile($media),
            $uniqueFileName->getPath()
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
