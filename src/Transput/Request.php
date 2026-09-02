<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Transput;

class Request implements RequestInterface
{
    public function __construct(
        protected \OxidEsales\Eshop\Core\Request $request
    ) {
    }

    public function getBoolRequestParameter(string $name): bool
    {
        /** @var string|int|null $value */
        $value = $this->request->getRequestParameter($name);
        return (bool)$value;
    }

    public function getStringRequestParameter(string $name, string $default = ''): string
    {
        $value = $this->request->getRequestEscapedParameter($name, $default);
        return is_string($value) ? $value : $default;
    }

    public function getIntRequestParameter(string $name): int
    {
        /** @var string|int|null $value */
        $value = $this->request->getRequestParameter($name);
        return (int)$value;
    }

    public function getArrayRequestParameter(string $name): array
    {
        $value = $this->request->getRequestParameter($name);

        return is_array($value) ? array_values(array_map('strval', $value)) : [];
    }
}
