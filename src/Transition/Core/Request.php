<?php

namespace OxidEsales\MediaLibrary\Transition\Core;

class Request implements RequestInterface
{
    public const REQUEST_PARAM_OVERLAY = 'overlay';
    public const REQUEST_PARAM_POPUP = 'popout';

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
}
