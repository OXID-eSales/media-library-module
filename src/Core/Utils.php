<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Core;

/**
 * Class Utils
 *
 * @mixin \OxidEsales\Eshop\Core\Utils
 */
class Utils extends Utils_parent
{
    public function showJsonAndExit($mMsg = null)
    {
        header('Content-Type: application/json');
        $this->showMessageAndExit(json_encode($mMsg));
    }
}
