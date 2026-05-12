<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Format;

use OxidEsales\MediaLibrary\Validation\Format\DTO\FileFormatInterface;

interface FileFormatRegistryInterface
{
    public function findByExtension(string $extension): ?FileFormatInterface;
}
