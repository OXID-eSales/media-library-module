<?php

namespace OxidEsales\MediaLibrary\Transition\Core;

use OxidEsales\MediaLibrary\Core\Utils;

class Response implements ResponseInterface
{
    /** @var Utils $language */
    private $utils;

    public function __construct(\OxidEsales\Eshop\Core\Utils $utils)
    {
        /** @var Utils $utils */
        $this->utils = $utils;
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