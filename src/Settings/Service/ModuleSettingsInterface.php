<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Settings\Service;

interface ModuleSettingsInterface
{
    public function getAlternativeImageUrl(): string;

    /**
     * @return array<string>
     */
    public function getAllowedExtensions(): array;
}
