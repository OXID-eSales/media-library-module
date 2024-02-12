<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace Image\Service;

use org\bovigo\vfs\vfsStream;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactoredInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailGeneratorInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailResourceInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailService;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Service\FileSystemServiceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Image\Service\ThumbnailService
 */
class ThumbnailServiceTest extends TestCase
{
    public function testDeleteMediaThumbnailsTriggersFilesystemDeleteByGlob(): void
    {
        $sut = $this->getSut(
            thumbnailResource: $thumbnailResourceStub = $this->createStub(ThumbnailResourceInterface::class),
            fileSystemService: $fileSystemServiceSpy = $this->createMock(FileSystemServiceInterface::class),
        );

        $mediaFileName = uniqid();
        $mediaFolderName = uniqid();
        $mediaStub = $this->createStub(MediaInterface::class);
        $mediaStub->method('getFileName')->willReturn($mediaFileName);
        $mediaStub->method('getFolderName')->willReturn($mediaFolderName);

        $thumbGlob = 'exampleThumbGlob';
        $thumbPath = 'exampleThumbPath';
        $thumbnailResourceStub->method('getThumbnailsGlob')->with($mediaFileName)->willReturn($thumbGlob);
        $thumbnailResourceStub->method('getPathToThumbnailFiles')->with($mediaFolderName)->willReturn($thumbPath);

        $fileSystemServiceSpy->expects($this->once())->method('deleteByGlob')->with($thumbPath, $thumbGlob);

        $sut->deleteMediaThumbnails($mediaStub);
    }

    public function testGetThumbnailUrlTriggerDefaultThumbnailCreation(): void
    {
        $vfsRootPath = vfsStream::setup('root', 0777, [])->url();

        $sut = $this->getSut(
            thumbnailResource: $thumbnailResourceMock = $this->createMock(ThumbnailResourceInterface::class),
            fileSystemService: $fileSystemSpy = $this->createMock(FileSystemServiceInterface::class),
            thumbnailGenerator: $thumbnailGeneratorSpy = $this->createMock(ThumbnailGeneratorInterface::class),
            imageResource: $imageResourceMock = $this->createMock(ImageResourceRefactoredInterface::class),
        );

        $folderName = 'someFolderName';
        $fileName = 'someFileName';

        $thumbnailFileName = 'someThumbnailName';
        $sizeStub = new ImageSize(100, 100);
        $thumbnailFolder = $vfsRootPath . '/thumbs';
        $thumbnailUrlFolder = 'someUrlToThumbFolder';
        $thumbnailResourceMock->method('getUrlToThumbnailFiles')->with($folderName)->willReturn($thumbnailUrlFolder);
        $thumbnailResourceMock->method('getPathToThumbnailFiles')->with($folderName)->willReturn($thumbnailFolder);
        $thumbnailResourceMock->method('getDefaultThumbnailSize')->willReturn($sizeStub);
        $thumbnailResourceMock->method('getThumbnailFileName')
            ->with($fileName, $sizeStub, true)
            ->willReturn($thumbnailFileName);

        $originalFolder = 'originalFolder';
        $imageResourceMock->method('getPathToMediaFiles')->with($folderName)->willReturn($originalFolder);

        $fileSystemSpy->expects($this->once())->method('ensureDirectory')->with($thumbnailFolder);
        $thumbnailGeneratorSpy->expects($this->once())->method('generateThumbnail')
            ->with(
                $originalFolder . '/' . $fileName,
                $thumbnailFolder . '/' . $thumbnailFileName,
                $sizeStub,
                true
            );

        $expectedUrl = $thumbnailUrlFolder . '/' . $thumbnailFileName;
        $this->assertSame($expectedUrl, $sut->ensureAndGetThumbnailUrl($folderName, $fileName));
    }

    public function getSut(
        ThumbnailResourceInterface $thumbnailResource = null,
        FileSystemServiceInterface $fileSystemService = null,
        ThumbnailGeneratorInterface $thumbnailGenerator = null,
        ImageResourceRefactoredInterface $imageResource = null,
    ): ThumbnailService {
        return new ThumbnailService(
            thumbnailResource: $thumbnailResource ?? $this->createStub(ThumbnailResourceInterface::class),
            fileSystemService: $fileSystemService ?? $this->createStub(FileSystemServiceInterface::class),
            thumbnailGenerator: $thumbnailGenerator ?? $this->createStub(ThumbnailGeneratorInterface::class),
            imageResource: $imageResource ?? $this->createStub(ImageResourceRefactoredInterface::class),
        );
    }
}
