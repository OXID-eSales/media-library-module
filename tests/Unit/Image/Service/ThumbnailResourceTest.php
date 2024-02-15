<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace Image\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailResource;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Image\Service\ThumbnailResource
 */
class ThumbnailResourceTest extends TestCase
{
    public static function getThumbnailFileNameDataProvider(): \Generator
    {
        $fileName = 'filename.gif';
        $fileNameHash = md5($fileName);
        yield "regular 100x100 nocrop" => [
            'originalFileName' => $fileName,
            'thumbnailSize' => new ImageSize(100, 100),
            'crop' => true,
            'expectedName' => $fileNameHash . '_thumb_100*100.jpg'
        ];

        yield "regular 100x100 crop" => [
            'originalFileName' => $fileName,
            'thumbnailSize' => new ImageSize(100, 100),
            'crop' => false,
            'expectedName' => $fileNameHash . '_thumb_100*100_nocrop.jpg'
        ];

        yield "regular 200x50 nocrop" => [
            'originalFileName' => $fileName,
            'thumbnailSize' => new ImageSize(200, 50),
            'crop' => true,
            'expectedName' => $fileNameHash . '_thumb_200*50.jpg'
        ];

        yield "regular 200x50 crop" => [
            'originalFileName' => $fileName,
            'thumbnailSize' => new ImageSize(200, 50),
            'crop' => false,
            'expectedName' => $fileNameHash . '_thumb_200*50_nocrop.jpg'
        ];
    }

    protected function getSut(
        ImageResourceInterface $imageResource = null,
    ) {
        return new ThumbnailResource(
            imageResource: $imageResource ?: $this->createStub(ImageResourceInterface::class),
        );
    }

    /** @dataProvider getThumbnailFileNameDataProvider */
    public function testGetThumbnailFileName(
        string $originalFileName,
        ImageSizeInterface $thumbnailSize,
        bool $crop,
        string $expectedName
    ): void {
        $sut = $this->getSut();

        $result = $sut->getThumbnailFileName(
            originalFileName: $originalFileName,
            thumbnailSize: $thumbnailSize,
            crop: $crop
        );

        $this->assertSame($expectedName, $result);
    }

    public function testGetThumbnailsGlob(): void
    {
        $sut = $this->getSut();

        $originalFilename = 'someExampleFilename.txt';
        $this->assertSame('8910f1d8c070ff09e13d4977fc339a29*.*', $sut->getThumbnailsGlob($originalFilename));
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
            imageResource: $imageResource = $this->createStub(ImageResourceInterface::class)
        );
        $imageResource->method('getPathToMediaFiles')->with('')->willReturn($mediaFilesPath);

        $this->assertSame($mediaFilesPath . '/thumbs', $sut->getPathToThumbnailFiles());
    }

    public function testGetPathToThumbnailFilesWithFolder(): void
    {
        $mediaFilesPath = 'somePathToMediaFilesWithFolder';
        $folder = uniqid();

        $sut = $this->getSut(
            imageResource: $imageResource = $this->createStub(ImageResourceInterface::class)
        );
        $imageResource->method('getPathToMediaFiles')->with($folder)->willReturn($mediaFilesPath);

        $this->assertSame($mediaFilesPath . '/thumbs', $sut->getPathToThumbnailFiles($folder));
    }

    public function testGetUrlToThumbnailFilesNoFolder(): void
    {
        $mediaFilesUrl = 'someUrlToMediaFiles';

        $sut = $this->getSut(
            imageResource: $imageResource = $this->createStub(ImageResourceInterface::class)
        );
        $imageResource->method('getUrlToMediaFiles')->with($this->isEmpty())->willReturn($mediaFilesUrl);

        $this->assertSame($mediaFilesUrl . '/thumbs', $sut->getUrlToThumbnailFiles());
    }

    public function testGetUrlToThumbnailFilesWithFolder(): void
    {
        $mediaFilesUrl = 'someUrlToMediaFiles';
        $folder = uniqid();

        $sut = $this->getSut(
            imageResource: $imageResource = $this->createStub(ImageResourceInterface::class)
        );
        $imageResource->method('getUrlToMediaFiles')->with($folder)->willReturn($mediaFilesUrl);

        $this->assertSame($mediaFilesUrl . '/thumbs', $sut->getUrlToThumbnailFiles($folder));
    }
}
