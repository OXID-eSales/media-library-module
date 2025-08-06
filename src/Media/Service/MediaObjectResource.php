<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Service;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;

class MediaObjectResource implements MediaObjectResourceInterface
{
    public function __construct(
        private readonly MediaResourceInterface $mediaResource,
    ) {
    }

    public function getPathToMedia(MediaInterface $media): string
    {
        return $this->mediaResource->getPathToMediaFile(
            folderName: $media->getFolderName(),
            fileName: $media->getFileName(),
        );
    }

    public function getUrlToMedia(MediaInterface $media): string
    {
        return $this->mediaResource->getUrlToMediaFile(
            folderName: $media->getFolderName(),
            fileName: $media->getFileName(),
        );
    }
}
