<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\ThumbnailGenerator;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\MediaLibrary\Service\FileSystemServiceInterface;

class SvgDriver implements ThumbnailGeneratorInterface
{
    public function __construct(
        protected FileSystemServiceInterface $fileSystemService
    ) {
    }

    public function isOriginSupported(string $sourcePath): bool
    {
        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        return $extension === 'svg';
    }

    public function generateThumbnail(
        string $sourcePath,
        string $thumbnailPath,
        ImageSizeInterface $thumbnailSize,
        bool $isCropRequired,
    ): void {
        $this->fileSystemService->copy($sourcePath, $thumbnailPath);
    }

    public function getThumbnailFileName(
        string $originalFileName,
        ImageSizeInterface $thumbnailSize,
        bool $isCropRequired
    ): string {
        return sprintf(
            '%s.svg',
            $this->getThumbnailFileHash($originalFileName),
        );
    }

    private function getThumbnailFileHash(string $originalFilename): string
    {
        return md5($originalFilename);
    }

    public function getThumbnailsGlob(string $originalFilename): string
    {
        return $this->getThumbnailFileHash($originalFilename) . '.svg';
    }
}
