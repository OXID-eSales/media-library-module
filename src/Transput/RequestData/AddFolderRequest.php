<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Transput\RequestData;

use OxidEsales\MediaLibrary\Transput\RequestInterface;

class AddFolderRequest implements AddFolderRequestInterface
{
    public const REQUEST_PARAM_NAME = 'name';

    public function __construct(private RequestInterface $request)
    {
    }

    public function getName(): string
    {
        return $this->request->getStringRequestParameter(self::REQUEST_PARAM_NAME);
    }
}