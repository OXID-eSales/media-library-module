<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Compatibility\Service;

use OxidEsales\MediaLibrary\Compatibility\DTO\MediaFileInformation;
use OxidEsales\MediaLibrary\Compatibility\DTO\MediaFileInformationInterface;
use OxidEsales\MediaLibrary\Compatibility\Service\MediaPathService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MediaPathServiceTest extends TestCase
{
    #[Test]
    #[DataProvider('pathDataProvider')]
    public function pathInformationCalculatedCorrectly(string $path, MediaFileInformationInterface $expected): void
    {
        $sut = new MediaPathService();
        $this->assertEquals(
            $expected,
            $sut->getMediaFileInformation($path)
        );
    }

    public static function pathDataProvider(): \Generator
    {
        yield 'empty path' => [
            'path' => '',
            'expected' => new MediaFileInformation('', ''),
        ];

        yield 'path with file name only' => [
            'path' => 'test.jpg',
            'expected' => new MediaFileInformation('test.jpg', ''),
        ];

        yield 'path with directory and file name' => [
            'path' => 'images/test.jpg',
            'expected' => new MediaFileInformation('test.jpg', 'images'),
        ];

        yield 'path with leading slash' => [
            'path' => '/images/test.jpg',
            'expected' => new MediaFileInformation('test.jpg', 'images'),
        ];

        yield 'path with multiple directories' => [
            'path' => 'images/subfolder/test.jpg',
            'expected' => new MediaFileInformation('test.jpg', 'subfolder'),
        ];

        yield 'path without filename' => [
            'path' => 'images/subfolder/',
            'expected' => new MediaFileInformation('', 'subfolder'),
        ];

        yield 'path as url' => [
            'path' => 'http://something.com/with/path/images/someimage.jpg',
            'expected' => new MediaFileInformation('someimage.jpg', 'images'),
        ];
    }
}
