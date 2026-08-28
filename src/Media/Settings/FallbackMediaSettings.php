<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Settings;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\MediaLibrary\Module;

class FallbackMediaSettings implements FallbackMediaSettingsInterface
{
    public const SETTING_FALLBACK_MEDIA_ID = 'ddoeMediaLibraryFallbackMediaId';

    public function __construct(
        private readonly ModuleSettingServiceInterface $moduleSettingService,
    ) {
    }

    public function getFallbackMediaId(): string
    {
        return $this->moduleSettingService->getString(
            name: self::SETTING_FALLBACK_MEDIA_ID,
            moduleId: Module::MODULE_ID
        )->toString();
    }

    public function saveFallbackMediaId(string $mediaId): void
    {
        $this->moduleSettingService->saveString(
            name: self::SETTING_FALLBACK_MEDIA_ID,
            value: $mediaId,
            moduleId: Module::MODULE_ID
        );
    }
}
