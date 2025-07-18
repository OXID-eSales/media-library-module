<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Compatibility\Service;

use OxidEsales\MediaLibrary\Compatibility\DTO\MediaFileInformation;
use OxidEsales\MediaLibrary\Compatibility\DTO\MediaFileInformationInterface;
use OxidEsales\MediaLibrary\Compatibility\Exception\UnknownPathFormatException;
use OxidEsales\MediaLibrary\Compatibility\Factory\MediaFileInformationFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MediaFileInformationFactoryTest extends TestCase
{
    #[Test]
    #[DataProvider('pathDataProvider')]
    public function pathInformationCalculatedCorrectly(string $path, MediaFileInformationInterface $expected): void
    {
        $sut = new MediaFileInformationFactory();
        $this->assertEquals(
            $expected,
            $sut->fromPath($path)
        );
    }

    public static function pathDataProvider(): \Generator
    {
        yield 'path with file name only' => [
            'path' => 'test.jpg',
            'expected' => new MediaFileInformation('test.jpg', ''),
        ];

        yield 'path with directory and file name' => [
            'path' => 'images/test.jpg',
            'expected' => new MediaFileInformation('test.jpg', 'images'),
        ];

        yield 'empty folder case' => [
            'path' => '/fileExample.gif',
            'expected' => new MediaFileInformation('fileExample.gif', ''),
        ];

        yield 'path in known directory' => [
            'path' => '//localhost.local/out/pictures/ddmedia/fileExample.gif',
            'expected' => new MediaFileInformation('fileExample.gif', ''),
        ];

        yield 'path in known directory with subdirectory' => [
            'path' => '//localhost.local/out/pictures/ddmedia/someFolder/fileExample.gif',
            'expected' => new MediaFileInformation('fileExample.gif', 'someFolder'),
        ];

        yield 'path in known dynamic directory' => [
            'path' => '{{oViewConf.getMediaUrl()}}/fileExample.gif',
            'expected' => new MediaFileInformation('fileExample.gif', ''),
        ];

        yield 'path in known dynamic directory with spaces' => [
            'path' => '{{ oViewConf.getMediaUrl() }}/fileExample.gif',
            'expected' => new MediaFileInformation('fileExample.gif', ''),
        ];

        yield 'path in known dynamic directory case insensitive' => [
            'path' => '{{ oViewConf.GETMEDIAURL() }}/fileExample.gif',
            'expected' => new MediaFileInformation('fileExample.gif', ''),
        ];

        yield 'path in known dynamic directory with subdirectory' => [
            'path' => '{{oViewConf.getMediaUrl()}}/someFolder/fileExample.gif',
            'expected' => new MediaFileInformation('fileExample.gif', 'someFolder'),
        ];
    }

    #[Test]
    #[DataProvider('badPathDataProvider')]
    public function pathInformationCalculatedWithError(string $path): void
    {
        $sut = new MediaFileInformationFactory();

        $this->expectException(UnknownPathFormatException::class);
        $sut->fromPath($path);
    }

    public static function badPathDataProvider(): \Generator
    {
        yield 'empty path' => [
            'path' => '',
        ];

        yield 'unknown path' => [
            'path' => 'some/path/to/something/fileExample.gif',
        ];

        yield 'full unknown path' => [
            'path' => '/some/path/to/something/fileExample.gif',
        ];

        yield 'unknown full path with subdirectory only' => [
            'path' => '/something/fileExample.gif',
        ];
    }
}
