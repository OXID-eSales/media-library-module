<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Transput;

use OxidEsales\Eshop\Core\Utils;

class Response implements ResponseInterface
{
    public function __construct(
        private Utils $utils
    ) {
    }

    public function responseAsJson(array $valueArray): void
    {
        $this->utils->setHeader('Content-Type: application/json; charset=UTF-8');
        $this->utils->showMessageAndExit(json_encode($valueArray));
    }

    public function responseAsJavaScript(string $value): void
    {
        $this->utils->setHeader('Content-Type: application/javascript; charset=UTF-8');
        $this->utils->showMessageAndExit($value);
    }

    public function responseAsTextHtml(string $value): void
    {
        $this->utils->setHeader('Content-Type: text/html; charset=UTF-8');
        $this->utils->showMessageAndExit($value);
    }
}
