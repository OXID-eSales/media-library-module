<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Unit\Image\Service;

use org\bovigo\vfs\vfsStream;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailGeneratorAggregateInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailResourceInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailService;
use OxidEsales\MediaLibrary\Image\ThumbnailGenerator\ThumbnailGeneratorInterface;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaResourceInterface;
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
            tgAgt: $thumbnailGeneratorAggregateStub = $this->createMock(ThumbnailGeneratorAggregateInterface::class),
            imageResource: $mediaResourceMock = $this->createMock(MediaResourceInterface::class),
        );

        $folderName = 'someFolderName';
        $fileName = 'someFileName';

        $thumbnailFileName = 'someThumbnailName';
        $defaultSizeStub = new ImageSize(100, 100);
        $thumbnailFolder = $vfsRootPath . '/thumbs';
        $thumbnailUrlFolder = 'someUrlToThumbFolder';
        $defaultCropFlag = true;

        $thumbnailResourceMock->method('getUrlToThumbnailFiles')->with($folderName)->willReturn($thumbnailUrlFolder);
        $thumbnailResourceMock->method('getPathToThumbnailFiles')->with($folderName)->willReturn($thumbnailFolder);
        $thumbnailResourceMock->method('getDefaultThumbnailSize')->willReturn($defaultSizeStub);

        $originalFolder = 'originalFolder';
        $mediaResourceMock->method('getPathToMediaFiles')->with($folderName)->willReturn($originalFolder);

        $fileSystemSpy->expects($this->once())->method('ensureDirectory')->with($thumbnailFolder);

        $thumbnailGeneratorSpy = $this->createMock(ThumbnailGeneratorInterface::class);
        $thumbnailGeneratorAggregateStub->method('getSupportedGenerator')->willReturn($thumbnailGeneratorSpy);
        $thumbnailGeneratorSpy->method('getThumbnailFileName')
            ->with($fileName, $defaultSizeStub, $defaultCropFlag)
            ->willReturn($thumbnailFileName);
        $thumbnailGeneratorSpy->expects($this->once())->method('generateThumbnail')
            ->with(
                $originalFolder . '/' . $fileName,
                $thumbnailFolder . '/' . $thumbnailFileName,
                $defaultSizeStub,
                $defaultCropFlag
            );

        $expectedUrl = $thumbnailUrlFolder . '/' . $thumbnailFileName;
        $this->assertSame($expectedUrl, $sut->ensureAndGetThumbnailUrl($folderName, $fileName));
    }

    public function getSut(
        ThumbnailResourceInterface $thumbnailResource = null,
        FileSystemServiceInterface $fileSystemService = null,
        ThumbnailGeneratorAggregateInterface $tgAgt = null,
        MediaResourceInterface $imageResource = null,
    ): ThumbnailService {
        return new ThumbnailService(
            thumbnailResource: $thumbnailResource ?? $this->createStub(ThumbnailResourceInterface::class),
            fileSystemService: $fileSystemService ?? $this->createStub(FileSystemServiceInterface::class),
            thumbnailGeneratorAggregate: $tgAgt ?? $this->createStub(ThumbnailGeneratorAggregateInterface::class),
            mediaResource: $imageResource ?? $this->createStub(MediaResourceInterface::class),
        );
    }
}
