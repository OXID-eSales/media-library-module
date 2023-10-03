<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Integration\Transition\Core;

use OxidEsales\MediaLibrary\Transition\Core\ViewConfig;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceInterface;
use OxidEsales\MediaLibrary\Tests\Integration\IntegrationTestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Core\ViewConfig
 */
class ViewConfigTest extends IntegrationTestCase
{
    public function testGetMediaUrl(): void
    {
        $imageResourceMock = $this->createMock(ImageResourceInterface::class);
        $imageResourceMock->method('getMediaUrl')->with('someFile')->willReturn('someFilePath');

        $sut = $this->createPartialMock(ViewConfig::class, ['getService']);
        $sut->method('getService')->willReturnMap([
            [ImageResourceInterface::class, $imageResourceMock]
        ]);

        $this->assertSame('someFilePath', $sut->getMediaUrl('someFile'));
    }
}
