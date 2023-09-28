<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Application\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\MediaLibrary\Core\Utils;
use OxidEsales\MediaLibrary\Transition\Core\LanguageInterface;

/**
 * Class MediaLangJs
 */
class MediaLangJs extends FrontendController
{
    /**
     * Init function
     */
    public function init()
    {
        $languages = $this->getService(LanguageInterface::class);

        header('Content-Type: application/javascript');

        /** @var Utils $oUtils */
        $oUtils = Registry::getUtils();
        $sJson = $oUtils->encodeJson($languages->getLanguageStringsArray());
        $oUtils->showMessageAndExit(";( function(g){ g.i18n = " . $sJson . "; })(window);");
    }
}
