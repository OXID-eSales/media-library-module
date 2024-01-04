<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Transput;

interface RequestInterface
{
    public function getBoolRequestParameter(string $name): bool;

    public function getStringRequestParameter(string $name, string $default = ''): string;

    public function getIntRequestParameter(string $name): int;
}
