<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Application\Controller\Admin;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\MediaLibrary\Transput\RequestData\UIRequestInterface;

/**
 * Class MediaWrapperController
 */
class MediaWrapperController extends MediaController
{
    public function init()
    {
        $this->addTplParam('oConf', Registry::getConfig());
        $this->addTplParam('request', $this->getService(UIRequestInterface::class));

        parent::init();
        $this->setTemplateName('@ddoemedialibrary/dialog/ddoemedia_wrapper');
    }
}
