<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace Image\Service;

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

    public function getSut(
        ThumbnailResourceInterface $thumbnailResource = null,
        FileSystemServiceInterface $fileSystemService = null,
    ): ThumbnailService {
        return new ThumbnailService(
            thumbnailResource: $thumbnailResource ?? $this->createStub(ThumbnailResourceInterface::class),
            fileSystemService: $fileSystemService ?? $this->createStub(FileSystemServiceInterface::class),
        );
    }
}
