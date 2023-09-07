<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Thumbnail\Service;

use Intervention\Image\ImageManager;

class ThumbnailGeneratorIntervention implements ThumbnailGeneratorInterface
{
    public function __construct(private readonly ImageManager $imageManager)
    {
    }
    public function generateThumbnail(
        string $sourcePath,
        string $thumbnailPath,
        int $thumbnailSize,
        bool $blCrop,
    ): void {

        $image = $this->imageManager->make($sourcePath);
        if ($blCrop) {
            $image->fit($thumbnailSize, $thumbnailSize, function ($constraint) {
                $constraint->upsize();
            });
        } else {
            $image->resize($thumbnailSize, $thumbnailSize, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
        $image->save($thumbnailPath);
    }
}
