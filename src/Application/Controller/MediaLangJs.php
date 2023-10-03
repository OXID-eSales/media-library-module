<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Application\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\MediaLibrary\Transition\Core\LanguageInterface;
use OxidEsales\MediaLibrary\Transition\Core\ResponseInterface;

/**
 * Class MediaLangJs
 */
class MediaLangJs extends FrontendController
{
    public function init()
    {
        $languages = $this->getService(LanguageInterface::class);
        $responseService = $this->getService(ResponseInterface::class);

        $jsonValue = json_encode($languages->getLanguageStringsArray());
        $responseService->responseAsJavaScript(";( function(g){ g.i18n = " . $jsonValue . "; })(window);");
    }
}
