<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\MediaLibrary\Transput\RequestData\UIRequestInterface;

/**
 * Class MediaWrapperController
 */
class MediaWrapperController extends AdminDetailsController
{
    /**
     * @return void
     */
    public function init()
    {
        $this->addTplParam('oConf', Registry::getConfig());
        $this->addTplParam('request', $this->getService(UIRequestInterface::class));

        parent::init();
        $this->setTemplateName('@ddoemedialibrary/dialog/ddoemedia_wrapper');
    }
}
