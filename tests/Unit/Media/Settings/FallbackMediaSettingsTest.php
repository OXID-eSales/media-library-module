<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Settings;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\MediaLibrary\Media\Settings\FallbackMediaSettings;
use OxidEsales\MediaLibrary\Module;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\UnicodeString;

class FallbackMediaSettingsTest extends TestCase
{
    #[Test]
    public function getDefaultMediaIdReturnsModuleSettingValue(): void
    {
        $moduleSettingsService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingsService->method('getString')
            ->with(FallbackMediaSettings::SETTING_FALLBACK_MEDIA_ID, Module::MODULE_ID)
            ->willReturn(new UnicodeString($settingValue = uniqid()));

        $sut = new FallbackMediaSettings(
            moduleSettingService: $moduleSettingsService,
        );

        $result = $sut->getFallbackMediaId();
        $this->assertSame($settingValue, $result);
    }
}
