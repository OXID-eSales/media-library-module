<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Settings\Service;

/**
 * @todo-medium segregate, move to respective domain
 */
interface ModuleSettingsInterface
{
    public function getAlternativeImageUrl(): string;

    /**
     * @return array<string>
     */
    public function getAllowedExtensions(): array;
}
