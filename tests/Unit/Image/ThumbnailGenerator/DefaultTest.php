<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Image\ThumbnailGenerator;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
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

    public function testGetThumbnailFileNameReturnsDefaultValue(): void
    {
        $sut = new DefaultDriver();
        $this->assertSame(
            'default.jpg',
            $sut->getThumbnailFileName(
                originalFileName: uniqid(),
                thumbnailSize: $this->createStub(ImageSizeInterface::class),
                isCropRequired: (bool)random_int(0, 1)
            )
        );
    }
}