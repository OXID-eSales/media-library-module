<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Unit\Image\DataTransfer;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImageSize::class)]
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

    #[DataProvider('isEmptyDataProvider')]
    public function testIsEmpty(int $width, int $height, bool $expected): void
    {
        $sut = new ImageSize($width, $height);
        $this->assertSame($expected, $sut->isEmpty());
    }

    public static function isEmptyDataProvider(): array
    {
        return [
            [0, 0, true],
            [0, 100, true],
            [100, 0, true],
            [10, 10, false],
        ];
    }

    #[DataProvider('getInFormatDataProvider')]
    public function testGetInFormat(
        int $width,
        int $height,
        ?string $format,
        ?string $emptyFormat,
        string $expectedResult
    ): void {
        $sut = new ImageSize($width, $height);
        $this->assertSame($expectedResult, $sut->getInFormat($format, $emptyFormat));
    }

    public static function getInFormatDataProvider(): array
    {
        return [
            [123, 321, '%dx%d', '', '123x321'],
            [123, 321, 'Known', '', 'Known'],
            [0, 0, 'Known', 'Unknown', 'Unknown'],
            [123, 0, '%dx%d', '%d:%d', '123:0'],
            [0, 321, '%dx%d', '%d:%d', '0:321'],
            [0, 321, '%dx%d', '', ''],
        ];
    }
}
