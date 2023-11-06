<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Transput;

class Request implements RequestInterface
{
    public const REQUEST_PARAM_FOLDER_ID = 'folderid';
    public const REQUEST_PARAM_OVERLAY = 'overlay';
    public const REQUEST_PARAM_POPUP = 'popout';
    public const REQUEST_PARAM_MEDIA_LIST_START_INDEX = 'start';

    public function __construct(
        private \OxidEsales\Eshop\Core\Request $request
    ) {
    }

    public function isOverlay(): bool
    {
        /** @var string|int|null $value */
        $value = $this->request->getRequestParameter(self::REQUEST_PARAM_OVERLAY, false);
        return (bool)$value;
    }

    public function isPopout(): bool
    {
        /** @var string|int|null $value */
        $value = $this->request->getRequestParameter(self::REQUEST_PARAM_POPUP, false);
        return (bool)$value;
    }

    public function getFolderId(): string
    {
        $value = $this->request->getRequestEscapedParameter(self::REQUEST_PARAM_FOLDER_ID);
        return is_string($value) ? $value : '';
    }

    public function getMediaListStartIndex(): int
    {
        /** @var string|int|null $value */
        $value = $this->request->getRequestParameter(self::REQUEST_PARAM_MEDIA_LIST_START_INDEX);
        return (int)$value;
    }
}
