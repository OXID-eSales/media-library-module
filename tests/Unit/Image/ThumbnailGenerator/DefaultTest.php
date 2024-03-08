<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Image\ThumbnailGenerator;

use OxidEsales\MediaLibrary\Image\ThumbnailGenerator\DefaultDriver;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Image\ThumbnailGenerator\DefaultDriver
 */
class DefaultTest extends TestCase
{
    public function testIsOriginSupportedAlwaysReturnTrue(): void
    {
        $sut = new DefaultDriver();
        $this->assertTrue($sut->isOriginSupported(uniqid()));
    }
}