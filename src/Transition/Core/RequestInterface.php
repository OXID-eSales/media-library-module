<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Transition\Core;

interface RequestInterface
{
    public function isOverlay(): bool;

    public function isPopout(): bool;
}
