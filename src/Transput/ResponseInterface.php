<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Transput;

interface ResponseInterface
{
    public function responseAsJson(array $valueArray): void;

    public function responseAsJavaScript(string $value): void;

    public function responseAsTextHtml(string $value): void;
}
