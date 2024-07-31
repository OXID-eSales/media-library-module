<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Unit\Image\Service;

use OxidEsales\MediaLibrary\Image\Service\ThumbnailResource;
use OxidEsales\MediaLibrary\Media\Service\MediaResourceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ThumbnailResource::class)]
class ThumbnailResourceTest extends TestCase
{
    protected function getSut(
        MediaResourceInterface $imageResource = null,
    ) {
        return new ThumbnailResource(
            mediaResource: $imageResource ?: $this->createStub(MediaResourceInterface::class),
        );
    }

    public function testGetDefaultThumbnailSize(): void
    {
        $sut = $this->getSut();

        $size = $sut->getDefaultThumbnailSize();

        $this->assertSame($sut::THUMBNAIL_DEFAULT_SIZE, $size->getWidth());
        $this->assertSame($sut::THUMBNAIL_DEFAULT_SIZE, $size->getHeight());
    }

    public static function getPathToThumbnailFilesDataProvider(): \Generator
    {
        $folder = uniqid();
        yield 'specific folder' => [
            'folder' => $folder,
            'expectedResult' => 'somePathToMediaFiles/' . $folder . '/thumbs'
        ];
    }

    public function testGetPathToThumbnailFilesNoFolder(): void
    {
        $mediaFilesPath = 'somePathToMediaFiles';

        $sut = $this->getSut(
            imageResource: $imageResource = $this->createMock(MediaResourceInterface::class)
        );
        $imageResource->method('getPathToMediaFiles')->with('')->willReturn($mediaFilesPath);

        $this->assertSame($mediaFilesPath . '/thumbs', $sut->getPathToThumbnailFiles());
    }

    public function testGetPathToThumbnailFilesWithFolder(): void
    {
        $mediaFilesPath = 'somePathToMediaFilesWithFolder';
        $folder = uniqid();

        $sut = $this->getSut(
            imageResource: $imageResource = $this->createMock(MediaResourceInterface::class)
        );
        $imageResource->method('getPathToMediaFiles')->with($folder)->willReturn($mediaFilesPath);

        $this->assertSame($mediaFilesPath . '/thumbs', $sut->getPathToThumbnailFiles($folder));
    }

    public function testGetUrlToThumbnailFilesNoFolder(): void
    {
        $mediaFilesUrl = 'someUrlToMediaFiles';

        $sut = $this->getSut(
            imageResource: $imageResource = $this->createMock(MediaResourceInterface::class)
        );
        $imageResource->method('getUrlToMediaFiles')->with($this->isEmpty())->willReturn($mediaFilesUrl);

        $this->assertSame($mediaFilesUrl . '/thumbs', $sut->getUrlToThumbnailFiles());
    }

    public function testGetUrlToThumbnailFilesWithFolder(): void
    {
        $mediaFilesUrlWithFolder = 'someUrlToMediaFiles';
        $folder = uniqid();

        $sut = $this->getSut(
            imageResource: $imageResourceMock = $this->createMock(MediaResourceInterface::class)
        );
        $imageResourceMock->method('getUrlToMediaFiles')->with($folder)->willReturn($mediaFilesUrlWithFolder);

        $this->assertSame($mediaFilesUrlWithFolder . '/thumbs', $sut->getUrlToThumbnailFiles($folder));
    }

    public function testGetPathToThumbnailFile(): void
    {
        $thumbFilesPath = 'somePathToThumbnailFile';
        $fileName = uniqid();
        $folder = uniqid();

        $sut = $this->createPartialMock(ThumbnailResource::class, ['getPathToThumbnailFiles']);
        $sut->method('getPathToThumbnailFiles')->with($folder)->willReturn($thumbFilesPath);

        $this->assertSame($thumbFilesPath . '/' . $fileName, $sut->getPathToThumbnailFile($fileName, $folder));
    }

    public function testGetPathToThumbnailFileWithoutFolder(): void
    {
        $thumbFilesPath = 'somePathToThumbnailFile';
        $fileName = uniqid();

        $sut = $this->createPartialMock(ThumbnailResource::class, ['getPathToThumbnailFiles']);
        $sut->method('getPathToThumbnailFiles')->with('')->willReturn($thumbFilesPath);

        $this->assertSame($thumbFilesPath . '/' . $fileName, $sut->getPathToThumbnailFile($fileName));
    }

    public function testGetUrlToThumbnailFile(): void
    {
        $mediaFilesUrlWithoutFolder = 'someUrlToThumbnailFiles';
        $fileName = uniqid();
        $folder = uniqid();

        $sut = $this->createPartialMock(ThumbnailResource::class, ['getUrlToThumbnailFiles']);
        $sut->method('getUrlToThumbnailFiles')->with($folder)->willReturn($mediaFilesUrlWithoutFolder);

        $this->assertSame(
            $mediaFilesUrlWithoutFolder . '/' . $fileName,
            $sut->getUrlToThumbnailFile($fileName, $folder)
        );
    }

    public function testGetUrlToThumbnailFileWithoutFolder(): void
    {
        $mediaFilesUrlWithoutFolder = 'someUrlToThumbnailFiles';
        $thumbnailFileName = uniqid();

        $sut = $this->createPartialMock(ThumbnailResource::class, ['getUrlToThumbnailFiles']);
        $sut->method('getUrlToThumbnailFiles')->with('')->willReturn($mediaFilesUrlWithoutFolder);

        $this->assertSame(
            $mediaFilesUrlWithoutFolder . '/' . $thumbnailFileName,
            $sut->getUrlToThumbnailFile($thumbnailFileName)
        );
    }
}
