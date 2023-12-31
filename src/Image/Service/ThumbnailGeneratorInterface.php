<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;

interface ThumbnailGeneratorInterface
{
    public function generateThumbnail(
        string $sourcePath,
        string $thumbnailPath,
        ImageSizeInterface $size,
        bool $blCrop,
    ): void;
}
