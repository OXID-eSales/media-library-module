<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Application\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\MediaLibrary\Core\Utils;

/**
 * Class WysiwygLangJs
 */
class MediaLangJs extends FrontendController
{
    /**
     * Init function
     */
    public function init()
    {
        /** @var \OxidEsales\MediaLibrary\Core\Language $oLang */
        $oLang = oxNew(Language::class);

        header('Content-Type: application/javascript');

        /** @var Utils $oUtils */
        $oUtils = Registry::getUtils();
        $sJson = $oUtils->encodeJson($oLang->getLanguageStrings());
        $oUtils->showMessageAndExit(";( function(g){ g.i18n = " . $sJson . "; })(window);");
    }
}
