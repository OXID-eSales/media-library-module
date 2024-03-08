<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\ThumbnailGenerator;

use Intervention\Image\ImageManager;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;

class InterventionDriver implements ThumbnailGeneratorInterface
{
    public function __construct(private readonly ImageManager $imageManager)
    {
    }

    public function isOriginSupported(string $sourcePath): bool
    {
        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'webp', 'gif', 'png', 'avif', 'bmp']);
    }

    public function generateThumbnail(
        string $sourcePath,
        string $thumbnailPath,
        ImageSizeInterface $size,
        bool $blCrop,
    ): void {
        $thumbnailWidth = $size->getWidth();
        $thumbnailHeight = $size->getHeight();

        $image = $this->imageManager->read($sourcePath);
        if ($blCrop) {
            $image->coverDown(
                width: $thumbnailWidth,
                height: $thumbnailHeight
            );
        } else {
            $image->scaleDown(
                width: $thumbnailWidth,
                height: $thumbnailHeight
            );
        }
        $image->save($thumbnailPath);
    }

    public function getThumbnailFileName(
        string $originalFileName,
        ImageSizeInterface $thumbnailSize,
        bool $isCropRequired
    ): string {
        return sprintf(
            '%s_thumb_%d*%d%s.%s',
            $this->getThumbnailFileHash($originalFileName),
            $thumbnailSize->getWidth(),
            $thumbnailSize->getHeight(),
            $isCropRequired ? '' : '_nocrop',
            $this->getExtensionFromFileName($originalFileName)
        );
    }

    private function getThumbnailFileHash(string $originalFilename): string
    {
        return md5($originalFilename);
    }

    protected function getExtensionFromFileName(string $fileName): string
    {
        return pathinfo($fileName, PATHINFO_EXTENSION);
    }
}
