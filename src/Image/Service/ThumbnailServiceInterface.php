<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Image\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;

interface ThumbnailServiceInterface
{
    public function deleteMediaThumbnails(MediaInterface $media): void;

    public function ensureAndGetThumbnailUrl(
        string $folderName,
        string $fileName,
        ?ImageSizeInterface $imageSize = null,
        bool $crop = true
    ): string;
}
