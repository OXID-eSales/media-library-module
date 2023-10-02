<?php

namespace OxidEsales\MediaLibrary\Transition\Core;

interface ResponseInterface
{
    public function responseAsJson(array $valueArray): void;

    public function responseAsJavaScript(string $value): void;
}
