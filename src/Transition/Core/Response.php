<?php

namespace OxidEsales\MediaLibrary\Transition\Core;

use OxidEsales\Eshop\Core\Utils;

class Response implements ResponseInterface
{
    public function __construct(
        private Utils $utils
    ) {
    }

    public function responseAsJson(array $valueArray): void
    {
        $this->utils->setHeader('Content-Type: application/json');
        $this->utils->showMessageAndExit(json_encode($valueArray));
    }

    public function responseAsJavaScript(string $value): void
    {
        $this->utils->setHeader('Content-Type: application/javascript');
        $this->utils->showMessageAndExit($value);
    }
}
