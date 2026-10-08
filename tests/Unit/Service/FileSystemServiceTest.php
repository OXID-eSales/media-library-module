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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileSystemService::class)]
class FileSystemServiceTest extends TestCase
{
    #[DataProvider('ensureDirectorySuccessCasesDataProvider')]
    #[Test]
    public function ensureDirectorySuccessful(string $pathExample): void
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

    #[Test]
    public function ensureDirectoryError(): void
    {
        $root = vfsStream::setup('root', 0444, [])->url();
        $path = $root . DIRECTORY_SEPARATOR . 'someDirectory';

        $sut = $this->getSut();

        $this->expectException(DirectoryCreationException::class);
        $sut->ensureDirectory($path);
    }

    #[Test]
    public function getImageSize(): void
    {
        $sut = $this->getSut();

        $size = $sut->getImageSize(__DIR__ . '/../../fixtures/img/image.gif');

        $this->assertSame(640, $size->getWidth());
        $this->assertSame(853, $size->getHeight());
    }

    #[Test]
    public function getImageSizeOnNotImageGivesZeros(): void
    {
        $sut = $this->getSut();

        $size = $sut->getImageSize(__DIR__ . '/../../fixtures/img/LICENSE');

        $this->assertSame(0, $size->getWidth());
        $this->assertSame(0, $size->getHeight());
    }

    #[Test]
    public function getImageSizeOnNotExistingFileGivesZeros(): void
    {
        $sut = $this->getSut();

        $size = $sut->getImageSize('random');

        $this->assertSame(0, $size->getWidth());
        $this->assertSame(0, $size->getHeight());
    }

    #[Test]
    public function deleteOneFile(): void
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

    #[Test]
    public function deleteDirectoryWithContent(): void
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

    #[Test]
    public function deleteByGlob(): void
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

    #[Test]
    public function renameFile(): void
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

    #[Test]
    public function renameFolder(): void
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

    #[Test]
    public function renameMovesFileToAnotherDirectory(): void
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

    #[Test]
    public function renameMovesFileToNotExistingDirectory(): void
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

    #[Test]
    public function copyFile(): void
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

    #[Test]
    public function copyToNotExistingFolderCreatesFolder(): void
    {
        $root = vfsStream::setup('root', 0777, [
            'file1.txt' => 'content1',
        ]);

        $sut = $this->getSut();

        $sut->copy($root->url() . '/' . 'file1.txt', $root->url() . '/someNewFolder/' . 'copiedFile1.txt');

        $this->assertTrue($root->hasChild('file1.txt'));
        $this->assertTrue($root->hasChild('someNewFolder/copiedFile1.txt'));
    }

    #[Test]
    public function getFileSize(): void
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

    #[Test]
    public function getMimeType(): void
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

    private function getSut(): FileSystemServiceInterface
    {
        return new FileSystemService();
    }
}
