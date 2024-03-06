<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\MediaLibrary\Module;

class ModuleSettings implements ModuleSettingsInterface
{
    public const SETTING_ALTERNATIVE_IMAGE_URL = 'ddoeMediaLibraryAlternativeImageDirectory';

    public function __construct(
        private ModuleSettingServiceInterface $moduleSettingService
    ) {
    }

    public function getAlternativeImageUrl(): string
    {
        return $this->getStringSettingValue(self::SETTING_ALTERNATIVE_IMAGE_URL);
    }

    protected function getStringSettingValue(string $key): string
    {
        return $this->moduleSettingService->getString($key, Module::MODULE_ID)->trim()->toString();
    }
}
