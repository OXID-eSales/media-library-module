<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Transput;

use OxidEsales\Eshop\Core\Utils;
use OxidEsales\MediaLibrary\Exception\ResponseCreationException;

class Response implements ResponseInterface
{
    public function __construct(
        private Utils $utils
    ) {
    }

    public function responseAsJson(array $valueArray): void
    {
        $this->utils->setHeader('Content-Type: application/json; charset=UTF-8');

        $responseJson = json_encode($valueArray);
        if ($responseJson === false) {
            throw new ResponseCreationException('Failed to encode response as JSON: ' . json_last_error_msg());
        }

        $this->utils->showMessageAndExit($responseJson);
    }

    public function errorResponseAsJson(int $code, string $message, array $valueArray): void
    {
        $this->utils->setHeader("HTTP/1.1 $code $message");
        $this->responseAsJson($valueArray);
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
