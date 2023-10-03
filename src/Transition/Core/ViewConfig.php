<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Transition\Core;

use OxidEsales\MediaLibrary\Image\Service\ImageResourceInterface;

/**
 * Class ViewConfig
 *
 * @mixin \OxidEsales\Eshop\Core\ViewConfig
 */
class ViewConfig extends ViewConfig_parent
{
    public function getMediaUrl($sFile = '')
    {
        $imageResource = $this->getService(ImageResourceInterface::class);
        return $imageResource->getMediaUrl($sFile);
    }
}
