<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\Service;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;

interface ThumbnailServiceInterface
{
    public function deleteMediaThumbnails(MediaInterface $media): void;
}
