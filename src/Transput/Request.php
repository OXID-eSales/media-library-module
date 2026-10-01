<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Transput;

use OxidEsales\EshopCommunity\Internal\Framework\Request\RequestInterface as ShopRequestInterface;

class Request implements RequestInterface
{
    public function __construct(
        protected ShopRequestInterface $request
    ) {
    }

    public function getBoolRequestParameter(string $name): bool
    {
        /** @var string|int|null $value */
        $value = $this->request->get($name);
        return (bool)$value;
    }

    public function getStringRequestParameter(string $name, string $default = ''): string
    {
        $value = $this->request->get($name, $default);
        return is_string($value) ? $value : $default;
    }

    public function getIntRequestParameter(string $name): int
    {
        /** @var string|int|null $value */
        $value = $this->request->get($name);
        return (int)$value;
    }

    public function getArrayRequestParameter(string $name): array
    {
        $value = $this->request->get($name);

        return is_array($value) ? array_values(array_map('strval', $value)) : [];
    }
}
