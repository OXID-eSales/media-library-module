<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Repository;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceInterface;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactoredInterface;
use OxidEsales\MediaLibrary\Media\DataType\Media;

class MediaFactory implements MediaFactoryInterface
{
    public function __construct(
        protected ImageResourceRefactoredInterface $imageResource
    ) {
    }

    public function fromDatabaseArray(array $item): Media
    {
        $size = explode("x", $item['DDIMAGESIZE']);
        $thumbnailUrl = $this->imageResource->calculateMediaThumbnailUrl(
            fileName: (string)$item['DDFILENAME'],
            fileType: (string)$item['DDFILETYPE']
        );

        return new Media(
            oxid: (string)$item['OXID'],
            fileName: (string)$item['DDFILENAME'],
            fileSize: (int)$item['DDFILESIZE'],
            fileType: (string)$item['DDFILETYPE'],
            thumbFileName: $thumbnailUrl,
            imageSize: new ImageSize(intval($size[0]), intval($size[1])),
            folderId: $item['DDFOLDERID']
        );
    }
}
