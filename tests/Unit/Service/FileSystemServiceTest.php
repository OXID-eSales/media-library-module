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

    public function testRenameFile(): void
    {
        $root = vfsStream::setup('root', 0777, [
            'file1.txt' => 'content1',
            'file2.txt' => 'content2',
        ]);

        $sut = $this->getSut();

        $sut->rename($root->url() . '/' . 'file1.txt', $root->url() . '/' . 'renamedFile1.txt');

        $this->assertFalse($root->hasChild('file1.txt'));
        $this->assertTrue($root->hasChild('renamedFile1.txt'));
        $this->assertTrue($root->hasChild('file2.txt'));
    }

    public function testRenameFolder(): void
    {
        $root = vfsStream::setup('root', 0777, [
            'file1.txt' => 'content1',
            'someFolder' => [
                'folderFile1.txt' => 'content',
            ]
        ]);

        $sut = $this->getSut();

        $sut->rename($root->url() . '/' . 'someFolder', $root->url() . '/' . 'someOtherFolder');

        $this->assertFalse($root->hasChild('someFolder'));
        $this->assertTrue($root->hasChild('someOtherFolder'));
        $this->assertTrue($root->hasChild('someOtherFolder/folderFile1.txt'));
    }

    public function testMoveFileWithRename(): void
    {
        $root = vfsStream::setup('root', 0777, [
            'file1.txt' => 'content1',
            'file2.txt' => 'content2',
            'someFolder' => [
            ]
        ]);

        $sut = $this->getSut();

        $sut->rename($root->url() . '/' . 'file1.txt', $root->url() . '/someFolder/' . 'movedFile1.txt');

        $this->assertFalse($root->hasChild('file1.txt'));
        $this->assertTrue($root->hasChild('file2.txt'));
        $this->assertTrue($root->hasChild('someFolder/movedFile1.txt'));
    }

    public function testMoveFileToNotExistingDirectory(): void
    {
        $root = vfsStream::setup('root', 0777, [
            'file1.txt' => 'content1',
            'file2.txt' => 'content2',
        ]);

        $sut = $this->getSut();

        $sut->rename($root->url() . '/' . 'file1.txt', $root->url() . '/someFolder/' . 'movedFile1.txt');

        $this->assertFalse($root->hasChild('file1.txt'));
        $this->assertTrue($root->hasChild('file2.txt'));
        $this->assertTrue($root->hasChild('someFolder/movedFile1.txt'));
    }

    public function testCopyFileWithCopy(): void
    {
        $root = vfsStream::setup('root', 0777, [
            'file1.txt' => 'content1',
            'file2.txt' => 'content2',
            'someFolder' => [
            ]
        ]);

        $sut = $this->getSut();

        $sut->copy($root->url() . '/' . 'file1.txt', $root->url() . '/someFolder/' . 'copiedFile1.txt');

        $this->assertTrue($root->hasChild('file1.txt'));
        $this->assertTrue($root->hasChild('file2.txt'));
        $this->assertTrue($root->hasChild('someFolder/copiedFile1.txt'));
    }

    public function testCopyToNotExistingFolderCreatesFolder(): void
    {
        $root = vfsStream::setup('root', 0777, [
            'file1.txt' => 'content1',
        ]);

        $sut = $this->getSut();

        $sut->copy($root->url() . '/' . 'file1.txt', $root->url() . '/someNewFolder/' . 'copiedFile1.txt');

        $this->assertTrue($root->hasChild('file1.txt'));
        $this->assertTrue($root->hasChild('someNewFolder/copiedFile1.txt'));
    }

    public function testGetFileSize(): void
    {
        $root = vfsStream::setup('root', 0777, [
            'file1.txt' => 'content1',
            'someFolder' => [
            ]
        ]);

        $sut = $this->getSut();

        $this->assertSame(8, $sut->getFileSize($root->getChild('file1.txt')->url()));
        $this->assertSame(0, $sut->getFileSize($root->getChild('someFolder')->url()));
        $this->assertSame(0, $sut->getFileSize('notExisting'));
    }

    public function testGetMimeType(): void
    {
        $root = vfsStream::setup('root', 0777, [
            'file1.txt' => 'content1',
            'someFolder' => [
            ]
        ]);

        $sut = $this->getSut();

        $this->assertSame('text/plain', $sut->getMimeType($root->getChild('file1.txt')->url()));
        $this->assertSame('', $sut->getMimeType($root->getChild('someFolder')->url()));
        $this->assertSame('', $sut->getMimeType('notExisting'));
    }

    public function getSut(): FileSystemService
    {
        return new FileSystemService();
    }
}
