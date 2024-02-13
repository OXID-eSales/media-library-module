<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Service;

use OxidEsales\MediaLibrary\Media\DataType\FrontendMedia;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;

interface FrontendMediaFactoryInterface
{
    public function createFromMedia(MediaInterface $media): FrontendMedia;
}
