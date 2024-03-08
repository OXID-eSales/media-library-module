<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Unit\Image\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailResource;
use OxidEsales\MediaLibrary\Media\Service\MediaResourceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Image\Service\ThumbnailResource
 */
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
            imageResource: $imageResource = $this->createStub(MediaResourceInterface::class)
        );
        $imageResource->method('getPathToMediaFiles')->with('')->willReturn($mediaFilesPath);

        $this->assertSame($mediaFilesPath . '/thumbs', $sut->getPathToThumbnailFiles());
    }

    public function testGetPathToThumbnailFilesWithFolder(): void
    {
        $mediaFilesPath = 'somePathToMediaFilesWithFolder';
        $folder = uniqid();

        $sut = $this->getSut(
            imageResource: $imageResource = $this->createStub(MediaResourceInterface::class)
        );
        $imageResource->method('getPathToMediaFiles')->with($folder)->willReturn($mediaFilesPath);

        $this->assertSame($mediaFilesPath . '/thumbs', $sut->getPathToThumbnailFiles($folder));
    }

    public function testGetUrlToThumbnailFilesNoFolder(): void
    {
        $mediaFilesUrl = 'someUrlToMediaFiles';

        $sut = $this->getSut(
            imageResource: $imageResource = $this->createStub(MediaResourceInterface::class)
        );
        $imageResource->method('getUrlToMediaFiles')->with($this->isEmpty())->willReturn($mediaFilesUrl);

        $this->assertSame($mediaFilesUrl . '/thumbs', $sut->getUrlToThumbnailFiles());
    }

    public function testGetUrlToThumbnailFilesWithFolder(): void
    {
        $mediaFilesUrl = 'someUrlToMediaFiles';
        $folder = uniqid();

        $sut = $this->getSut(
            imageResource: $imageResource = $this->createStub(MediaResourceInterface::class)
        );
        $imageResource->method('getUrlToMediaFiles')->with($folder)->willReturn($mediaFilesUrl);

        $this->assertSame($mediaFilesUrl . '/thumbs', $sut->getUrlToThumbnailFiles($folder));
    }
}
