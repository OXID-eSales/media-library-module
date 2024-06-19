<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Settings\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\MediaLibrary\Module;
use OxidEsales\MediaLibrary\Settings\Service\ModuleSettings;
use OxidEsales\MediaLibrary\Settings\Service\ModuleSettingsInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\UnicodeString;

class ModuleSettingsTest extends TestCase
{
    public function testGetAlternativeImageUrl(): void
    {
        $sut = $this->getSut(
            moduleSettingService: $settingService = $this->createMock(ModuleSettingServiceInterface::class)
        );

        $expectedResult = uniqid();
        $settingService->method('getString')
            ->with(ModuleSettings::SETTING_ALTERNATIVE_IMAGE_URL, Module::MODULE_ID)
            ->willReturn(new UnicodeString(" {$expectedResult} "));

        $this->assertEquals($expectedResult, $sut->getAlternativeImageUrl());
    }

    public function testGetAllowedExtensions(): void
    {
        $sut = $this->getSut();
        $extensions = $sut->getAllowedExtensions();

        $this->assertNotEmpty($extensions);
        $this->assertTrue(in_array('jpg', $extensions));
        $this->assertTrue(in_array('gif', $extensions));
    }

    public function getSut(
        ModuleSettingServiceInterface $moduleSettingService = null
    ): ModuleSettingsInterface {
        return new ModuleSettings(
            moduleSettingService: $moduleSettingService ?? $this->createStub(ModuleSettingServiceInterface::class),
        );
    }
}
