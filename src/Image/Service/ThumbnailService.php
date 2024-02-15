<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Service\FileSystemServiceInterface;
use Symfony\Component\Filesystem\Path;

class ThumbnailService implements ThumbnailServiceInterface
{
    public function __construct(
        protected ThumbnailResourceInterface $thumbnailResource,
        protected FileSystemServiceInterface $fileSystemService,
        protected ThumbnailGeneratorInterface $thumbnailGenerator,
        protected ImageResourceInterface $imageResource,
    ) {
    }

    public function deleteMediaThumbnails(MediaInterface $media): void
    {
        $this->fileSystemService->deleteByGlob(
            inPath: $this->thumbnailResource->getPathToThumbnailFiles($media->getFolderName()),
            globTargetToDelete: $this->thumbnailResource->getThumbnailsGlob($media->getFileName())
        );
    }

    public function ensureAndGetThumbnailUrl(
        string $folderName,
        string $fileName,
        ImageSizeInterface $imageSize = null,
        bool $crop = true
    ): string {
        $thumbnailFileName = $this->thumbnailResource->getThumbnailFileName(
            originalFileName: $fileName,
            thumbnailSize: $imageSize ?? $this->thumbnailResource->getDefaultThumbnailSize(),
            crop: $crop
        );

        $thumbnailDirectoryPath = $this->thumbnailResource->getPathToThumbnailFiles($folderName);
        $thumbnailPath = Path::join($thumbnailDirectoryPath, $thumbnailFileName);
        if (!is_file($thumbnailPath)) {
            $this->fileSystemService->ensureDirectory($thumbnailDirectoryPath);
            $this->thumbnailGenerator->generateThumbnail(
                sourcePath: Path::join($this->imageResource->getPathToMediaFiles($folderName), $fileName),
                thumbnailPath: $thumbnailPath,
                size: $imageSize ?? $this->thumbnailResource->getDefaultThumbnailSize(),
                blCrop: $crop
            );
        }

        return Path::join($this->thumbnailResource->getUrlToThumbnailFiles($folderName), $thumbnailFileName);
    }
}
