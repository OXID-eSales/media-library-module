<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Integration\Core;

use OxidEsales\MediaLibrary\Tests\Integration\IntegrationTestCase;
use OxidEsales\MediaLibrary\Service\Media;

/**
 * Class ddVisualEditorOxViewConfigTest
 */
class ViewConfigTest extends IntegrationTestCase
{
    public function testGetMediaUrl(): void
    {
        $mediaMock = $this->createPartialMock(Media::class, ['getMediaUrl']);
        $mediaMock->method('getMediaUrl')->with('someFile')->willReturn('someFilePath');

        $sut = $this->createPartialMock(
            \OxidEsales\MediaLibrary\Core\ViewConfig::class,
            ['getService']
        );
        $sut->expects($this->any())->method('getService')
            ->with(Media::class)
            ->willReturn($mediaMock);

        $this->assertSame('someFilePath', $sut->getMediaUrl('someFile'));
    }
}
