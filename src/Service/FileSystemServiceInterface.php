<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;

/**
 * @todo-medium segregate if needed, move to relevant domain
 */
interface FileSystemServiceInterface
{
    public function ensureDirectory(string $path): bool;

    public function getImageSize(string $filePath): ImageSizeInterface;

    public function delete(string $targetToDelete): void;

    public function deleteByGlob(string $inPath, string $globTargetToDelete): void;

    public function rename(string $oldPath, string $newPath): void;

    public function moveUploadedFile(string $from, string $to): void;

    public function getFileSize(string $filePath): int;

    public function getMimeType(string $filePath): string;

    public function copy(string $originalFile, string $destinationFile): void;
}
