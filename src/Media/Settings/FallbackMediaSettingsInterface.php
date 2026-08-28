<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Media\Settings;

interface FallbackMediaSettingsInterface
{
    public function getFallbackMediaId(): string;

    public function saveFallbackMediaId(string $mediaId): void;
}
