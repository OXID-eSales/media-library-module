<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Media\Facade;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;

interface MediaFacadeInterface
{
    public function registerForPreload(string ...$mediaIds): void;

    /**
     * @throws MediaNotFoundException
     */
    public function getMedia(string $mediaId): MediaInterface;

    /**
     * @throws MediaNotFoundException
     */
    public function getMediaUrl(string $mediaId): string;
}
