<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Integration\Core;

use OxidEsales\MediaLibrary\Image\Service\ImageResource;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceInterface;
use OxidEsales\MediaLibrary\Tests\Integration\IntegrationTestCase;

/**
 * Class ddVisualEditorOxViewConfigTest
 */
class ViewConfigTest extends IntegrationTestCase
{
    public function testGetMediaUrl(): void
    {
        $imageResourceMock = $this->createPartialMock(imageResource::class, ['getMediaUrl']);
        $imageResourceMock->method('getMediaUrl')->with('someFile')->willReturn('someFilePath');

        $this->assertSame('someFilePath', $imageResourceMock->getMediaUrl('someFile'));
    }
}
