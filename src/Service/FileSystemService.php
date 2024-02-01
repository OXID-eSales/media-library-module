<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Service;

use OxidEsales\MediaLibrary\Exception\DirectoryCreationException;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;

class FileSystemService implements FileSystemServiceInterface
{
    public function ensureDirectory(string $path): bool
    {
        if (!is_dir($path) && !mkdir(directory: $path, recursive: true)) {
            throw new DirectoryCreationException();
        }

        return true;
    }

    public function getImageSize(string $filePath): ImageSize
    {
        $result = new ImageSize(0, 0);

        $imageData = @getimagesize($filePath);
        if ($imageData !== false) {
            $result = new ImageSize($imageData[0], $imageData[1]);
        }

        return $result;
    }
}
