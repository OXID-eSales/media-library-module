<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Integration\Image\ThumbnailGenerator;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use org\bovigo\vfs\vfsStream;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\MediaLibrary\Image\ThumbnailGenerator\DefaultDriver;
use OxidEsales\MediaLibrary\Image\ThumbnailGenerator\InterventionDriver;
use OxidEsales\MediaLibrary\Tests\Integration\IntegrationTestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Image\ThumbnailGenerator\InterventionDriver
 */
class InterventionDriverTest extends IntegrationTestCase
{
    /**
     * @dataProvider getThumbnailDataProvider
     */
    public function testGenerateThumbnail(
        $sourceWidth,
        $sourceHeight,
        $thumbnailSize,
        $expectedThumbnailWidth,
        $expectedThumbnailHeight,
        $cropThumbnail
    ): void {
        $rootPath = vfsStream::setup()->url();

        $imageManager = new ImageManager(new Driver());
        $sut = new InterventionDriver($imageManager);

        $sourcePath = $rootPath . '/source.jpg';
        $img = $imageManager->create($sourceWidth, $sourceHeight);
        $img->save($sourcePath);

        $thumbnailPath = $rootPath . '/thumbnail.jpg';
        $sut->generateThumbnail(
            $sourcePath,
            $thumbnailPath,
            new ImageSize($thumbnailSize, $thumbnailSize),
            $cropThumbnail
        );

        self::assertFileExists($thumbnailPath);

        $resultThumbnailImage = $imageManager->read($thumbnailPath);
        self::assertSame($expectedThumbnailWidth, $resultThumbnailImage->width());
        self::assertSame($expectedThumbnailHeight, $resultThumbnailImage->height());
    }

    public static function getThumbnailDataProvider(): array
    {
        return [
            'cropped_thumbnail_of_size_800x800_when_bigger_source_size_landscape' => [
                'sourceWidth' => 950,
                'sourceHeight' => 900,
                'thumbnailSize' => 800,
                'expectedThumbnailWidth' => 800,
                'expectedThumbnailHeight' => 800,
                'cropThumbnail' => true,
            ],
            'cropped_thumbnail_of_size_500x500_when_smaller_source_size_landscape' => [
                'sourceWidth' => 600,
                'sourceHeight' => 500,
                'thumbnailSize' => 800,
                'expectedThumbnailWidth' => 500,
                'expectedThumbnailHeight' => 500,
                'cropThumbnail' => true,
            ],
            'no_cropped_thumbnail_of_size_800x500_when_bigger_source_size_landscape' => [
                'sourceWidth' => 800,
                'sourceHeight' => 500,
                'thumbnailSize' => 800,
                'expectedThumbnailWidth' => 800,
                'expectedThumbnailHeight' => 500,
                'cropThumbnail' => false,
            ],
            'no_cropped_thumbnail_of_size_200x300_when_bigger_source_size_potrait' => [
                'sourceWidth' => 1000,
                'sourceHeight' => 1500,
                'thumbnailSize' => 300,
                'expectedThumbnailWidth' => 200,
                'expectedThumbnailHeight' => 300,
                'cropThumbnail' => false,
            ],
            'no_cropped_thumbnail_of_size_500x100_when_smaller_source_size_landscape' => [
                'sourceWidth' => 500,
                'sourceHeight' => 100,
                'thumbnailSize' => 800,
                'expectedThumbnailWidth' => 500,
                'expectedThumbnailHeight' => 100,
                'cropThumbnail' => false,
            ],
            'no_cropped_thumbnail_of_size_500x100_when_smaller_source_size_potrait' => [
                'sourceWidth' => 100,
                'sourceHeight' => 150,
                'thumbnailSize' => 300,
                'expectedThumbnailWidth' => 100,
                'expectedThumbnailHeight' => 150,
                'cropThumbnail' => false,
            ],
        ];
    }

    /** @dataProvider fileTypesDataProvider */
    public function testIsOriginSupported(string $filePath, bool $expectedResult): void
    {
        $imageManager = new ImageManager(new Driver());
        $sut = new InterventionDriver($imageManager);

        $this->assertSame($expectedResult, $sut->isOriginSupported($filePath));
        $this->assertSame($expectedResult, $sut->isOriginSupported(strtoupper($filePath)));
    }

    public static function fileTypesDataProvider(): \Generator
    {
        yield "jpg" => [
            'filePath' => 'someFileName.jpg',
            'expectedResult' => true,
        ];

        yield "jpeg" => [
            'filePath' => 'someFileName.jpeg',
            'expectedResult' => true,
        ];

        yield "webp" => [
            'filePath' => 'someFileName.webp',
            'expectedResult' => true,
        ];

        yield "gif" => [
            'filePath' => 'someFileName.gif',
            'expectedResult' => true,
        ];

        yield "png" => [
            'filePath' => 'someFileName.png',
            'expectedResult' => true,
        ];

        yield "avif" => [
            'filePath' => 'someFileName.avif',
            'expectedResult' => true,
        ];

        yield "bmp" => [
            'filePath' => 'someFileName.bmp',
            'expectedResult' => true,
        ];

        yield "tiff" => [
            'filePath' => 'someFileName.tiff',
            'expectedResult' => false,
        ];

        yield "heic" => [
            'filePath' => 'someFileName.heic',
            'expectedResult' => false,
        ];

        yield "mp3" => [
            'filePath' => 'someFileName.mp3',
            'expectedResult' => false,
        ];

        yield "doc" => [
            'filePath' => 'someFileName.doc',
            'expectedResult' => false,
        ];

        yield "svg" => [
            'filePath' => 'someFileName.svg',
            'expectedResult' => false,
        ];
    }

    /** @dataProvider getThumbnailFileNameDataProvider */
    public function testGetThumbnailFileName(
        string $originalFileName,
        ImageSizeInterface $thumbnailSize,
        bool $crop,
        string $expectedName
    ): void {
        $imageManager = new ImageManager(new Driver());
        $sut = new InterventionDriver($imageManager);

        $result = $sut->getThumbnailFileName(
            originalFileName: $originalFileName,
            thumbnailSize: $thumbnailSize,
            isCropRequired: $crop
        );

        $this->assertSame($expectedName, $result);
    }

    public static function getThumbnailFileNameDataProvider(): \Generator
    {
        $fileName = 'filename.jpg';
        $fileNameHash = md5($fileName);
        yield "regular jpg 100x100 nocrop" => [
            'originalFileName' => $fileName,
            'thumbnailSize' => new ImageSize(100, 100),
            'crop' => true,
            'expectedName' => $fileNameHash . '_thumb_100*100.jpg'
        ];

        yield "regular jpg 100x100 crop" => [
            'originalFileName' => $fileName,
            'thumbnailSize' => new ImageSize(100, 100),
            'crop' => false,
            'expectedName' => $fileNameHash . '_thumb_100*100_nocrop.jpg'
        ];

        yield "regular jpg 200x50 nocrop" => [
            'originalFileName' => $fileName,
            'thumbnailSize' => new ImageSize(200, 50),
            'crop' => true,
            'expectedName' => $fileNameHash . '_thumb_200*50.jpg'
        ];

        yield "regular jpg 200x50 crop" => [
            'originalFileName' => $fileName,
            'thumbnailSize' => new ImageSize(200, 50),
            'crop' => false,
            'expectedName' => $fileNameHash . '_thumb_200*50_nocrop.jpg'
        ];

        $specialExtensionFileName = 'filename.xxx';
        $specialExtensionFileNameHash = md5($specialExtensionFileName);
        yield "extension save check" => [
            'originalFileName' => $specialExtensionFileName,
            'thumbnailSize' => new ImageSize(100, 100),
            'crop' => true,
            'expectedName' => $specialExtensionFileNameHash . '_thumb_100*100.xxx'
        ];
    }

    public function testGetThumbnailsGlob(): void
    {
        $imageManager = new ImageManager(new Driver());
        $sut = new InterventionDriver($imageManager);

        $originalFilename = 'someExampleFilename.txt';
        $this->assertSame('8910f1d8c070ff09e13d4977fc339a29*.*', $sut->getThumbnailsGlob($originalFilename));
    }
}
