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

    public function ensureDirectorySuccessCasesDataProvider(): \Generator
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
}
