<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Validation\Format;

use OxidEsales\MediaLibrary\Validation\Format\DTO\FileFormatInterface;

interface FileFormatRegistryInterface
{
    public function findByExtension(string $extension): ?FileFormatInterface;
}
