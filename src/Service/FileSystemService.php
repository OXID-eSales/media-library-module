<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Service;

use OxidEsales\MediaLibrary\Exception\DirectoryCreationException;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

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

    public function delete(string $targetToDelete): void
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove($targetToDelete);
    }

    public function deleteByGlob(string $inPath, string $globTargetToDelete): void
    {
        $finder = new Finder();
        $files = $finder->in($inPath)->files()->name($globTargetToDelete);

        $fileSystem = new Filesystem();
        $fileSystem->remove($files);
    }

    public function rename(string $oldPath, string $newPath): void
    {
        $this->ensureDirectory(dirname($newPath));

        $fileSystem = new Filesystem();
        $fileSystem->rename($oldPath, $newPath);
    }

    /**
     * @codeCoverageIgnore Its a proxy and complicated to test, so precisely checking this one once manually is enough
     */
    public function moveUploadedFile(string $from, string $to): void
    {
        move_uploaded_file($from, $to);
    }
}
