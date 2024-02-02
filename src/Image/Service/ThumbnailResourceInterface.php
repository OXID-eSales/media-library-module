<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use Symfony\Component\Filesystem\Path;

interface ThumbnailResourceInterface
{
    public function calculateMediaThumbnailUrl(string $fileName, string $fileType): string;

    public function getThumbnailFileName(
        string $originalFileName,
        ImageSizeInterface $thumbnailSize,
        bool $crop
    ): string;

    public function getDefaultThumbnailSize(): ImageSizeInterface;

    public function getPathToThumbnailFiles(string $folder);
}
