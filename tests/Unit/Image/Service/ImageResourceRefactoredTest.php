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
use OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactored;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactored
 */
class ImageResourceRefactoredTest extends TestCase
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

    public function testGetPathToMediaFiles(): void
    {
        $sut = $this->getSut(
            shopConfig: $shopConfigStub = $this->createStub(Config::class)
        );
        $shopConfigStub->method('getConfigParam')->with('sShopDir')->willReturn('someShopDir');

        $this->assertSame('someShopDir/' . ImageResourceRefactored::MEDIA_PATH, $sut->getPathToMediaFiles());
    }

    protected function getSut(
        Config $shopConfig = null,
        ImageResourceInterface $oldImageResource = null,
    ) {
        return new ImageResourceRefactored(
            shopConfig: $shopConfig ?: $this->createStub(Config::class),
            oldImageResource: $oldImageResource ?: $this->createStub(ImageResourceInterface::class),
        );
    }

    public function testGetPathToMediaFilesWithSubdirectory(): void
    {
        $sut = $this->getSut(
            shopConfig: $shopConfigStub = $this->createStub(Config::class)
        );
        $shopConfigStub->method('getConfigParam')->with('sShopDir')->willReturn('someShopDir');

        $subDirectory = '/some/sub/directory';
        $this->assertSame(
            'someShopDir/' . ImageResourceRefactored::MEDIA_PATH . $subDirectory,
            $sut->getPathToMediaFiles($subDirectory)
        );
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
}
