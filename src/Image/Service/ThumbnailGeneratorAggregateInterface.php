<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\MediaLibrary\Image\ThumbnailGenerator\ThumbnailGeneratorInterface;

interface ThumbnailGeneratorAggregateInterface
{
    public function generateThumbnail(
        string $sourcePath,
        string $thumbnailPath,
        ImageSizeInterface $thumbnailSize,
        bool $isCropRequired,
    ): void;

    public function getSupportedGenerator(string $sourcePath): ThumbnailGeneratorInterface;
}
