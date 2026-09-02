<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Codeception\Support\Helper;

use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\MediaLibrary\Media\Settings\FallbackMediaSettings;
use OxidEsales\MediaLibrary\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

final class Acceptance extends \Codeception\Module
{
    public function grabFallbackMediaId(): string
    {
        return $this->getModuleSettingService()->getString(
            name: FallbackMediaSettings::SETTING_FALLBACK_MEDIA_ID,
            moduleId: Module::MODULE_ID
        )->toString();
    }

    public function setFallbackMediaId(string $mediaId): void
    {
        $this->getModuleSettingService()->saveString(
            name: FallbackMediaSettings::SETTING_FALLBACK_MEDIA_ID,
            value: $mediaId,
            moduleId: Module::MODULE_ID
        );
    }

    private function getModuleSettingService(): ModuleSettingServiceInterface
    {
        /** @var ModuleSettingServiceInterface $moduleSettingService */
        $moduleSettingService = ContainerFacade::get(ModuleSettingServiceInterface::class);

        return $moduleSettingService;
    }
}
