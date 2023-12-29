<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\Service;

use Intervention\Image\ImageManager;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;

class ThumbnailGeneratorIntervention implements ThumbnailGeneratorInterface
{
    public function __construct(private readonly ImageManager $imageManager)
    {
    }

    public function generateThumbnail(
        string $sourcePath,
        string $thumbnailPath,
        ImageSizeInterface $size,
        bool $blCrop,
    ): void {
        $thumbnailWidth = $size->getWidth();
        $thumbnailHeight = $size->getHeight();

        $image = $this->imageManager->make($sourcePath);
        if ($blCrop) {
            $image->fit($thumbnailWidth, $thumbnailHeight, function ($constraint) {
                $constraint->upsize();
            });
        } else {
            $image->resize($thumbnailWidth, $thumbnailHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
        $image->save($thumbnailPath);
    }
}
