<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Media\Repository;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;

/**
 * Interface for a repository that retrieves media with bulk ids preloading.
 * It allows doing less database queries when you need to access multiple media items.
 */
interface PreloadMediaRepositoryInterface
{
    public function registerForPreload(string $mediaId): void;

    public function getMediaById(string $mediaId): MediaInterface;
}
