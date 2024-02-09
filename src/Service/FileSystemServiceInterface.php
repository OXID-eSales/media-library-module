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

    public function delete(string $targetToDelete): void;

    public function deleteByGlob(string $inPath, string $globTargetToDelete): void;

    public function rename(string $oldPath, string $newPath): void;

    /**
     * @codeCoverageIgnore Its a proxy and complicated to test, so precisely checking this one once manually is enough
     */
    public function moveUploadedFile(string $from, string $to): void;
}
