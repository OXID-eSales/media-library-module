<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Language\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\MediaLibrary\Language\Core\LanguageInterface;
use OxidEsales\MediaLibrary\Transput\ResponseInterface;

/**
 * @deprecated will be removed in next major use the API route /api/oeml-translations to get
 * the object with translations. The route will be used with new LanguageInterface in js.
 */
class MediaLangJs extends FrontendController
{
    /**
     * @return void
     */
    public function init()
    {
        $languages = $this->getService(LanguageInterface::class);
        $responseService = $this->getService(ResponseInterface::class);

        $jsonValue = json_encode($languages->getLanguageStringsArray());
        $responseService->responseAsJavaScript(";( function(g){ g.i18n = " . $jsonValue . "; })(window);");
    }
}
