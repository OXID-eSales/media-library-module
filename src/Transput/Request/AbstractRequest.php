<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Transput\Request;

class AbstractRequest
{
    public function __construct(
        protected \OxidEsales\Eshop\Core\Request $request
    ) {
    }
}