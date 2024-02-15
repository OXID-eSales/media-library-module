<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Integration\Transition\Core;

use OxidEsales\MediaLibrary\Image\Service\ImageResourceInterface;
use OxidEsales\MediaLibrary\Tests\Integration\IntegrationTestCase;
use OxidEsales\MediaLibrary\Transition\Core\ViewConfig;

/**
 * @covers \OxidEsales\MediaLibrary\Transition\Core\ViewConfig
 */
class ViewConfigTest extends IntegrationTestCase
{
    public function testGetMediaUrl(): void
    {
        $imageResourceMock = $this->createMock(ImageResourceInterface::class);
        $imageResourceMock->method('getUrlToMediaFiles')->willReturn('someFilePath');

        /** @var ViewConfig $sut */
        $sut = $this->createPartialMock(oxNew(\OxidEsales\Eshop\Core\ViewConfig::class)::class, ['getService']);
        $sut->method('getService')->willReturnMap([
            [ImageResourceInterface::class, $imageResourceMock]
        ]);

        $this->assertSame('someFilePath', $sut->getMediaUrl());
    }
}
