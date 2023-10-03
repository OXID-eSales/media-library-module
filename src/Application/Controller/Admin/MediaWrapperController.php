<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Application\Controller\Admin;

use OxidEsales\Eshop\Core\Registry;

/**
 * Class MediaWrapperController
 */
class MediaWrapperController extends MediaController
{
    public function init()
    {
        $request = Registry::getRequest();

        $this->addTplParam('oConf', Registry::getConfig());
        $this->addTplParam('request', [
            'overlay' => $request->getRequestParameter('overlay') ?: 0,
            'popout' => $request->getRequestParameter('popout') ?: 0
        ]);

        parent::init();
        $this->setTemplateName('@ddoemedialibrary/dialog/ddoemedia_wrapper');
    }
}
