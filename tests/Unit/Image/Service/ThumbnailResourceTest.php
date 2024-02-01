<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace Image\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailResource;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactored
 */
class ThumbnailResourceTest extends TestCase
{
    public static function mediaThumbnailUrlDataProvider(): \Generator
    {
        yield 'image' => [
            'fileName' => 'someFilename.gif',
            'fileType' => 'notDirectory',
            'expected' => 'thumbgenerated.gif'
        ];

        yield 'directory' => [
            'fileName' => 'someFilename.gif',
            'fileType' => 'directory',
            'expected' => ''
        ];
    }

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

    /** @dataProvider mediaThumbnailUrlDataProvider */
    public function testCalculateMediaThumbnailUrl(string $fileName, string $fileType, string $expected): void
    {
        $sut = $this->getSut(
            oldImageResource: $oldImageResource = $this->createMock(ImageResourceInterface::class)
        );
        $oldImageResource->method('getThumbnailUrl')->willReturn('thumbgenerated.gif');

        $this->assertSame($expected, $sut->calculateMediaThumbnailUrl(fileName: $fileName, fileType: $fileType));
    }

    protected function getSut(
        Config $shopConfig = null,
        ImageResourceInterface $oldImageResource = null,
    ) {
        return new ThumbnailResource(
            oldImageResource: $oldImageResource ?: $this->createStub(ImageResourceInterface::class),
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

    public function testGetDefaultThumbnailSize(): void
    {
        $sut = $this->getSut();

        $size = $sut->getDefaultThumbnailSize();

        $this->assertSame($sut::THUMBNAIL_DEFAULT_SIZE, $size->getWidth());
        $this->assertSame($sut::THUMBNAIL_DEFAULT_SIZE, $size->getHeight());
    }
}
