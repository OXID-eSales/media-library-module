<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Unit\Image\DataTransfer;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use PHPUnit\Framework\TestCase;

class ImageSizeTest extends TestCase
{
    public function testGetWidth(): void
    {
        $size = new ImageSize(500, 195);
        self::assertEquals(500, $size->getWidth());
    }

    public function testGetHeight(): void
    {
        $size = new ImageSize(185, 600);
        self::assertEquals(600, $size->getHeight());
    }
}
