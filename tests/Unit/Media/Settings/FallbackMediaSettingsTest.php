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
    public function getFallbackMediaId(): void
    {
        $moduleSettingServiceMock = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingServiceMock->method('getString')
            ->with(FallbackMediaSettings::SETTING_FALLBACK_MEDIA_ID, Module::MODULE_ID)
            ->willReturn(new UnicodeString($settingValue = uniqid()));

        $sut = $this->getSut(moduleSettingService: $moduleSettingServiceMock);

        $this->assertSame($settingValue, $sut->getFallbackMediaId());
    }

    #[Test]
    public function saveFallbackMediaId(): void
    {
        $mediaId = uniqid();

        $moduleSettingServiceSpy = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingServiceSpy->expects($this->once())
            ->method('saveString')
            ->with(FallbackMediaSettings::SETTING_FALLBACK_MEDIA_ID, $mediaId, Module::MODULE_ID);

        $sut = $this->getSut(moduleSettingService: $moduleSettingServiceSpy);

        $sut->saveFallbackMediaId($mediaId);
    }

    private function getSut(
        ?ModuleSettingServiceInterface $moduleSettingService = null,
    ): FallbackMediaSettings {
        $moduleSettingService ??= $this->createStub(ModuleSettingServiceInterface::class);

        return new FallbackMediaSettings(
            moduleSettingService: $moduleSettingService,
        );
    }
}
