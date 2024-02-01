<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;

interface FileSystemServiceInterface
{
    public function ensureDirectory(string $path): bool;

    public function getImageSize(string $filePath): ImageSize;
}
