<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Factory;

use OxidEsales\MediaLibrary\Media\DataType\MediaAltTextInterface;

interface MediaAltTextFactoryInterface
{
    public function create(string $objectId, int $languageId, string $text): MediaAltTextInterface;
}
