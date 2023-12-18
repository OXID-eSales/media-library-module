<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Integration\Image\Service;

use Intervention\Image\ImageManager;
use org\bovigo\vfs\vfsStream;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Tests\Integration\IntegrationTestCase;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailGeneratorIntervention;

class ThumbnailGeneratorInterventionTest extends IntegrationTestCase
{
    private $vfsRootUrl;
    public function setUp(): void
    {
        $this->vfsRootUrl = vfsStream::setup()->url();
        parent::setUp();
    }

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
        $imageManager = new ImageManager();
        $thumbnailGenerator = new ThumbnailGeneratorIntervention($imageManager);

        $sourcePath = $this->vfsRootUrl . '/source.jpg';
        $img = $imageManager->canvas($sourceWidth, $sourceHeight);
        $img->save($sourcePath);

        $thumbnailPath = $this->vfsRootUrl . '/thumbnail.jpg';
        $thumbnailGenerator->generateThumbnail(
            $sourcePath,
            $thumbnailPath,
            new ImageSize($thumbnailSize, $thumbnailSize),
            $cropThumbnail
        );

        self::assertFileExists($thumbnailPath);

        $resultThumbnailImage = $imageManager->make($thumbnailPath);
        self::assertSame($expectedThumbnailWidth, $resultThumbnailImage->width());
        self::assertSame($expectedThumbnailHeight, $resultThumbnailImage->height());
    }


    public static function getThumbnailDataProvider()
    {
        return [
            'cropped_thumbnail_of_size_800x800_when_bigger_source_size_landscape' => [
                'sourceWidth'       => 950,
                'sourceHeight'      => 900,
                'thumbnailSize'     => 800,
                'expectedThumbnailWidth'    => 800,
                'expectedThumbnailHeight'   => 800,
                'cropThumbnail'             => true,
            ],
            'cropped_thumbnail_of_size_500x500_when_smaller_source_size_landscape' => [
                'sourceWidth'       => 600,
                'sourceHeight'      => 500,
                'thumbnailSize'     => 800,
                'expectedThumbnailWidth'    => 500,
                'expectedThumbnailHeight'   => 500,
                'cropThumbnail'             => true,
            ],
            'no_cropped_thumbnail_of_size_800x500_when_bigger_source_size_landscape' => [
                'sourceWidth'       => 800,
                'sourceHeight'      => 500,
                'thumbnailSize'     => 800,
                'expectedThumbnailWidth'    => 800,
                'expectedThumbnailHeight'   => 500,
                'cropThumbnail'             => false,
            ],
            'no_cropped_thumbnail_of_size_200x300_when_bigger_source_size_potrait' => [
                'sourceWidth'       => 1000,
                'sourceHeight'      => 1500,
                'thumbnailSize'     => 300,
                'expectedThumbnailWidth'    => 200,
                'expectedThumbnailHeight'   => 300,
                'cropThumbnail'             => false,
            ],
            'no_cropped_thumbnail_of_size_500x100_when_smaller_source_size_landscape' => [
                'sourceWidth'       => 500,
                'sourceHeight'      => 100,
                'thumbnailSize'     => 800,
                'expectedThumbnailWidth'    => 500,
                'expectedThumbnailHeight'   => 100,
                'cropThumbnail'             => false,
            ],
            'no_cropped_thumbnail_of_size_500x100_when_smaller_source_size_potrait' => [
                'sourceWidth'       => 100,
                'sourceHeight'      => 150,
                'thumbnailSize'     => 300,
                'expectedThumbnailWidth'    => 100,
                'expectedThumbnailHeight'   => 150,
                'cropThumbnail'             => false,
            ],
        ];
    }
}
