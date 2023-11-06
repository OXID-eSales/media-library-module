<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Repository;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Media\DataType\Media;

class MediaFactory implements MediaFactoryInterface
{
    public function fromDatabaseArray(array $item): Media
    {
        $size = explode("x", $item['DDIMAGESIZE']);
        return new Media(
            oxid: (string)$item['OXID'],
            fileName: (string)$item['DDFILENAME'],
            fileSize: (int)$item['DDFILESIZE'],
            fileType: (string)$item['DDFILETYPE'],
            thumbFileName: (string)$item['DDTHUMB'],
            imageSize: new ImageSize(intval($size[0]), intval($size[1])),
            folderId: $item['DDFOLDERID']
        );
    }
}
