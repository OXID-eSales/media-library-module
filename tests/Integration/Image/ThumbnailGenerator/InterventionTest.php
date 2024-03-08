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
use OxidEsales\MediaLibrary\Image\ThumbnailGenerator\Intervention;
use OxidEsales\MediaLibrary\Tests\Integration\IntegrationTestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Image\ThumbnailGenerator\Intervention
 */
class InterventionTest extends IntegrationTestCase
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
        $sut = new Intervention($imageManager);

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
        $sut = new Intervention($imageManager);

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
}
