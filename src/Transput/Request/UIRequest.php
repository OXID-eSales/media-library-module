<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Transput\Request;

class UIRequest extends AbstractRequest implements UIRequestInterface
{
    public const REQUEST_PARAM_FOLDER_ID = 'folderid';
    public const REQUEST_PARAM_OVERLAY = 'overlay';
    public const REQUEST_PARAM_POPUP = 'popout';
    public const REQUEST_PARAM_MEDIA_LIST_START_INDEX = 'start';

    public function isOverlay(): bool
    {
        /** @var string|int|null $value */
        $value = $this->request->getRequestParameter(self::REQUEST_PARAM_OVERLAY);
        return (bool)$value;
    }

    public function isPopout(): bool
    {
        /** @var string|int|null $value */
        $value = $this->request->getRequestParameter(self::REQUEST_PARAM_POPUP);
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
