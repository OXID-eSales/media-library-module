<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Service;

use org\bovigo\vfs\vfsStream;
use OxidEsales\MediaLibrary\Exception\DirectoryCreationException;
use OxidEsales\MediaLibrary\Service\FileSystemService;
use PHPUnit\Framework\TestCase;

class FileSystemServiceTest extends TestCase
{
    /** @dataProvider ensureDirectorySuccessCasesDataProvider */
    public function testEnsureDirectorySuccessful(string $pathExample): void
    {
        $root = vfsStream::setup('root', 0777, [])->url();
        $path = $root . DIRECTORY_SEPARATOR . $pathExample;

        $sut = new FileSystemService();
        $this->assertTrue($sut->ensureDirectory($path));
        $this->assertTrue(is_dir($path));
    }

    public static function ensureDirectorySuccessCasesDataProvider(): \Generator
    {
        yield ['pathExample' => 'someDirectory'];
        yield ['pathExample' => 'someDirectory/withSubDirectory'];
    }

    public function testEnsureDirectoryError(): void
    {
        $root = vfsStream::setup('root', 0444, [])->url();
        $path = $root . DIRECTORY_SEPARATOR . 'someDirectory';

        $sut = new FileSystemService();

        $this->expectException(DirectoryCreationException::class);
        $sut->ensureDirectory($path);
    }

    public function testGetImageSize(): void
    {
        $sut = new FileSystemService();

        $size = $sut->getImageSize(__DIR__ . '/../../fixtures/img/image.gif');

        $this->assertSame(640, $size->getWidth());
        $this->assertSame(853, $size->getHeight());
    }

    public function testGetImageSizeOnNotImageGivesZeros(): void
    {
        $sut = new FileSystemService();

        $size = $sut->getImageSize(__DIR__ . '/../../fixtures/img/LICENSE');

        $this->assertSame(0, $size->getWidth());
        $this->assertSame(0, $size->getHeight());
    }

    public function testGetImageSizeOnNotExistingFileGivesZeros(): void
    {
        $sut = new FileSystemService();

        $size = $sut->getImageSize('random');

        $this->assertSame(0, $size->getWidth());
        $this->assertSame(0, $size->getHeight());
    }
}
