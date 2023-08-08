<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Application\Controller\Admin;

use OxidEsales\Eshop\Core\Registry;

/**
 * Class WysiwygMediaWrapper
 */
class MediaWrapperController extends MediaController
{
    /**
     * Current class template name.
     *
     * @var string
     */
    protected $_sThisTemplate = '@ddoemedialibrary/dialog/ddoemedia_wrapper';

    public function init()
    {
        $request = Registry::getRequest();

        $this->_aViewData["oConf"] = Registry::getConfig();
        $this->_aViewData["request"]["overlay"] = $request->getRequestParameter('overlay') ?: 0;
        $this->_aViewData["request"]["popout"] = $request->getRequestParameter('popout') ?: 0;

        parent::init();
    }
}
