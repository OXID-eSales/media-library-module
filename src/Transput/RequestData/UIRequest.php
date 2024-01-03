<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Transput\RequestData;

use OxidEsales\MediaLibrary\Transput\RequestInterface;

class UIRequest implements UIRequestInterface
{
    public const REQUEST_PARAM_FOLDER_ID = 'folderid';
    public const REQUEST_PARAM_OVERLAY = 'overlay';
    public const REQUEST_PARAM_POPUP = 'popout';
    public const REQUEST_PARAM_MEDIA_LIST_START_INDEX = 'start';

    public function __construct(protected RequestInterface $request)
    {
    }

    public function isOverlay(): bool
    {
        return $this->request->getBoolRequestParameter(self::REQUEST_PARAM_OVERLAY);
    }

    public function isPopout(): bool
    {
        return $this->request->getBoolRequestParameter(self::REQUEST_PARAM_POPUP);
    }

    public function getFolderId(): string
    {
        return $this->request->getStringRequestParameter(self::REQUEST_PARAM_FOLDER_ID);
    }

    public function getMediaListStartIndex(): int
    {
        return $this->request->getIntRequestParameter(self::REQUEST_PARAM_MEDIA_LIST_START_INDEX);
    }
}
