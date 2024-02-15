<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Transition\Core;

use OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactoredInterface;

/**
 * Class ViewConfig
 *
 * @mixin \OxidEsales\Eshop\Core\ViewConfig
 */
class ViewConfig extends ViewConfig_parent
{
    public function getMediaUrl()
    {
        $imageResource = $this->getService(ImageResourceRefactoredInterface::class);
        return $imageResource->getUrlToMediaFiles();
    }
}
