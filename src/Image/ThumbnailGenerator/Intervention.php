<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\ThumbnailGenerator;

use Intervention\Image\ImageManager;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;

class Intervention implements ThumbnailGeneratorInterface
{
    public function __construct(private readonly ImageManager $imageManager)
    {
    }

    public function isOriginSupported(string $sourcePath): bool
    {
        return true;
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
}
