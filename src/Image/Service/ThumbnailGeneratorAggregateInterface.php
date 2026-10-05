<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Image\Service;

use OxidEsales\MediaLibrary\Image\ThumbnailGenerator\ThumbnailGeneratorInterface;

interface ThumbnailGeneratorAggregateInterface
{
    public function getSupportedGenerator(string $sourcePath): ThumbnailGeneratorInterface;
}
