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
use OxidEsales\MediaLibrary\Service\FileSystemServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class FileSystemServiceTest extends TestCase
{
    /** @dataProvider ensureDirectorySuccessCasesDataProvider */
    public function testEnsureDirectorySuccessful(string $pathExample): void
    {
        $root = vfsStream::setup('root', 0777, [])->url();
        $path = $root . DIRECTORY_SEPARATOR . $pathExample;

        $sut = $this->getSut();
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

        $sut = $this->getSut();

        $this->expectException(DirectoryCreationException::class);
        $sut->ensureDirectory($path);
    }

    public function testGetImageSize(): void
    {
        $sut = $this->getSut();

        $size = $sut->getImageSize(__DIR__ . '/../../fixtures/img/image.gif');

        $this->assertSame(640, $size->getWidth());
        $this->assertSame(853, $size->getHeight());
    }

    public function testGetImageSizeOnNotImageGivesZeros(): void
    {
        $sut = $this->getSut();

        $size = $sut->getImageSize(__DIR__ . '/../../fixtures/img/LICENSE');

        $this->assertSame(0, $size->getWidth());
        $this->assertSame(0, $size->getHeight());
    }

    public function testGetImageSizeOnNotExistingFileGivesZeros(): void
    {
        $sut = $this->getSut();

        $size = $sut->getImageSize('random');

        $this->assertSame(0, $size->getWidth());
        $this->assertSame(0, $size->getHeight());
    }

    public function testDeleteOneFile(): void
    {
        $root = vfsStream::setup('root', 0777, [
            'file1.txt' => 'content1',
            'file2.txt' => 'content2',
            'file3.txt' => 'content3',
        ]);

        $sut = $this->getSut();

        $sut->delete($root->url() . '/file2.txt');

        $this->assertTrue($root->hasChild('file1.txt'));
        $this->assertFalse($root->hasChild('file2.txt'));
        $this->assertTrue($root->hasChild('file3.txt'));
    }

    public function testDeleteDirectoryWithContent(): void
    {
        $directoryName = 'someDirectory';

        $root = vfsStream::setup('root', 0777, [
            'file1.txt' => 'content',
            $directoryName => [
                'subfile1.txt' => 'content',
                'anotherDirectory' => [
                    'subsubfile.txt' => 'content'
                ]
            ]
        ]);

        $this->assertTrue($root->hasChild($directoryName));

        $sut = $this->getSut();

        $sut->delete($root->url() . '/' . $directoryName);

        $this->assertTrue($root->hasChild('file1.txt'));
        $this->assertFalse($root->hasChild($directoryName));
    }

    public function testDeleteByGlob(): void
    {
        $root = vfsStream::setup('root', 0777, [
            'file1.txt' => 'content1',
            'file_with_something.txt' => 'content2',
            'file.txt' => 'content3',
        ]);

        $sut = $this->getSut();

        $sut->deleteByGlob($root->url(), 'file_*.*');

        $this->assertTrue($root->hasChild('file1.txt'));
        $this->assertFalse($root->hasChild('file_with_something.txt'));
        $this->assertTrue($root->hasChild('file.txt'));
    }

    public function getSut(): FileSystemService {
        return new FileSystemService();
    }
}
