<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Thumbnail\Service;

interface ThumbnailGeneratorInterface
{
    public function generateThumbnail(
        string $sourcePath,
        string $thumbnailPath,
        int $thumbnailSize,
        bool $blCrop,
    ): void;
}
