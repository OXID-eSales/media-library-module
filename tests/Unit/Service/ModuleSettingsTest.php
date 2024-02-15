<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\MediaLibrary\Module;
use OxidEsales\MediaLibrary\Service\ModuleSettings;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\UnicodeString;

class ModuleSettingsTest extends TestCase
{
    public function testGetAlternativeImageUrl(): void
    {
        $sut = new ModuleSettings(
            moduleSettingService: $settingService = $this->createMock(ModuleSettingServiceInterface::class),
        );

        $expectedResult = uniqid();
        $settingService->method('getString')
            ->with(ModuleSettings::SETTING_ALTERNATIVE_IMAGE_URL, Module::MODULE_ID)
            ->willReturn(new UnicodeString(" {$expectedResult} "));

        $this->assertEquals($expectedResult, $sut->getAlternativeImageUrl());
    }
}
