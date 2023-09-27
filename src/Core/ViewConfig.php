<?php

/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law.
 *
 * Any unauthorized use of this software will be prosecuted by
 * civil and criminal law.
 *
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2017
 * @version       OXID eSales Visual CMS
 */

namespace OxidEsales\MediaLibrary\Core;

/**
 * Class ViewConfig
 *
 * @mixin \OxidEsales\Eshop\Core\ViewConfig
 */
class ViewConfig extends ViewConfig_parent
{
    public function getMediaUrl($sFile = '')
    {
        $imageResource = $this->getService('OxidEsales\MediaLibrary\Image\Service\ImageResourceInterface');
        return $imageResource->getMediaUrl($sFile);
    }
}
