<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\ThumbnailGenerator;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;

interface ThumbnailGeneratorInterface
{
    public function isOriginSupported(string $sourcePath): bool;

    public function generateThumbnail(
        string $sourcePath,
        string $thumbnailPath,
        ImageSizeInterface $size,
        bool $blCrop,
    ): void;
}
