<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\ThumbnailGenerator;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;

class DefaultDriver implements ThumbnailGeneratorInterface
{
    public function isOriginSupported(string $sourcePath): bool
    {
        return true;
    }

    public function generateThumbnail(
        string $sourcePath,
        string $thumbnailPath,
        ImageSizeInterface $thumbnailSize,
        bool $isCropRequired,
    ): void {
        copy(__DIR__ . '/../../../assets/out/src/img/default.svg', $thumbnailPath);
    }

    public function getThumbnailFileName(
        string $originalFileName,
        ImageSizeInterface $thumbnailSize,
        bool $isCropRequired
    ): string {
        return 'default.svg';
    }

    public function getThumbnailsGlob(string $originalFilename): string
    {
        return 'default.svg';
    }
}
