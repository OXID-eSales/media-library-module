<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Service;

use OxidEsales\MediaLibrary\Image\Service\ThumbnailServiceInterface;
use OxidEsales\MediaLibrary\Media\DataType\FrontendMedia;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;

class FrontendMediaFactory implements FrontendMediaFactoryInterface
{
    public function __construct(
        protected ThumbnailServiceInterface $thumbnailService
    ) {
    }

    public function createFromMedia(MediaInterface $media): FrontendMedia
    {
        return new FrontendMedia(
            id: $media->getOxid(),
            file: $media->getFileName(),
            filetype: $media->getFileType(),
            filesize: $media->getFileSize(),
            thumb: $this->getMediaDefaultThumbnail($media),
            imageSize: $media->getImageSize()->getInFormat("%dx%d", '')
        );
    }

    private function getMediaDefaultThumbnail(MediaInterface $media): string
    {
        $result = '';

        if (!$media->isDirectory()) {
            $result = $this->thumbnailService->ensureAndGetThumbnailUrl(
                folderName: $media->getFolderName(),
                fileName: $media->getFileName(),
            );
        }

        return $result;
    }
}
