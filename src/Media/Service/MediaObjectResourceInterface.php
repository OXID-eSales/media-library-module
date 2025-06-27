<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Media\Service;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;

interface MediaObjectResourceInterface
{
    public function getPathToMedia(MediaInterface $media): string;

    public function getUrlToMedia(MediaInterface $media): string;
}
