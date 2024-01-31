<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Service;

use OxidEsales\MediaLibrary\Exception\DirectoryCreationException;

class FileSystemService implements FileSystemServiceInterface
{
    public function ensureDirectory(string $path): bool
    {
        if (!is_dir($path) && !mkdir(directory: $path, recursive: true)) {
            throw new DirectoryCreationException();
        }

        return true;
    }
}
