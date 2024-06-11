<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaResourceInterface;
use OxidEsales\MediaLibrary\Service\FileSystemServiceInterface;
use Symfony\Component\Filesystem\Path;

class ThumbnailService implements ThumbnailServiceInterface
{
    public function __construct(
        protected ThumbnailResourceInterface $thumbnailResource,
        protected FileSystemServiceInterface $fileSystemService,
        protected ThumbnailGeneratorAggregateInterface $thumbnailGeneratorAggregate,
        protected MediaResourceInterface $mediaResource,
    ) {
    }

    public function deleteMediaThumbnails(MediaInterface $media): void
    {
        $thumbnailGenerator = $this->thumbnailGeneratorAggregate->getSupportedGenerator($media->getFileName());

        $this->fileSystemService->deleteByGlob(
            inPath: $this->thumbnailResource->getPathToThumbnailFiles($media->getFolderName()),
            globTargetToDelete: $thumbnailGenerator->getThumbnailsGlob($media->getFileName())
        );
    }

    public function ensureAndGetThumbnailUrl(
        string $folderName,
        string $fileName,
        ImageSizeInterface $imageSize = null,
        bool $crop = true
    ): string {
        $sourcePath = $this->mediaResource->getPathToMediaFile($folderName, $fileName);
        $thumbnailGenerator = $this->thumbnailGeneratorAggregate->getSupportedGenerator($sourcePath);

        $thumbnailFileName = $thumbnailGenerator->getThumbnailFileName(
            originalFileName: $fileName,
            thumbnailSize: $imageSize ?? $this->thumbnailResource->getDefaultThumbnailSize(),
            isCropRequired: $crop
        );

        $thumbnailPath = $this->thumbnailResource->getPathToThumbnailFile($thumbnailFileName, $folderName);
        if (!is_file($thumbnailPath)) {
            $this->ensureThumbnailDirectoryExist($folderName);
            $thumbnailGenerator->generateThumbnail(
                sourcePath: $sourcePath,
                thumbnailPath: $thumbnailPath,
                thumbnailSize: $imageSize ?? $this->thumbnailResource->getDefaultThumbnailSize(),
                isCropRequired: $crop
            );
        }

        return $this->thumbnailResource->getUrlToThumbnailFile($thumbnailFileName, $folderName);
    }

    private function ensureThumbnailDirectoryExist(string $folderName): void
    {
        $thumbnailDirectoryPath = $this->thumbnailResource->getPathToThumbnailFiles($folderName);
        $this->fileSystemService->ensureDirectory($thumbnailDirectoryPath);
    }
}
