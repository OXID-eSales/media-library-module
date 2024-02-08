<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\Service;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Service\FileSystemServiceInterface;

class ThumbnailService implements ThumbnailServiceInterface
{
    public function __construct(
        protected ThumbnailResourceInterface $thumbnailResource,
        protected FileSystemServiceInterface $fileSystemService,
    ) {
    }

    public function deleteMediaThumbnails(MediaInterface $media): void
    {
        $this->fileSystemService->deleteByGlob(
            inPath: $this->thumbnailResource->getPathToThumbnailFiles($media->getFolderName()),
            globTargetToDelete: $this->thumbnailResource->getThumbnailsGlob($media->getFileName())
        );
    }
}
