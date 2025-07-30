<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\DataType;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Media\DataType\FrontendMedia;
use OxidEsales\MediaLibrary\Media\DataType\Media;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Media::class)]
class MediaTest extends TestCase
{
    public function testGetters(): void
    {
        $imageSize = new ImageSize(100, 100);

        $sut = new Media(
            oxid: 'someOxid',
            fileName: 'filename.jpg',
            fileSize: 25,
            fileType: 'image/gif',
            imageSize: $imageSize,
            folderId: 'someFolderId',
            folderName: 'someFolderName'
        );

        $this->assertSame('someOxid', $sut->getOxid());
        $this->assertSame('filename.jpg', $sut->getFileName());
        $this->assertSame(25, $sut->getFileSize());
        $this->assertSame('image/gif', $sut->getFileType());
        $this->assertSame($imageSize, $sut->getImageSize());
        $this->assertSame('someFolderId', $sut->getFolderId());
        $this->assertSame('someFolderName', $sut->getFolderName());
        $this->assertSame([], $sut->getAltTexts());
    }

    public function testOptionalDefaults(): void
    {
        $sut = new Media(
            oxid: 'someOxid',
            fileName: 'someFileName.jpg'
        );

        $this->assertSame('someOxid', $sut->getOxid());
        $this->assertSame('someFileName.jpg', $sut->getFileName());
        $this->assertSame(0, $sut->getFileSize());
        $this->assertSame('', $sut->getFileType());
        $this->assertEquals(new ImageSize(0, 0), $sut->getImageSize());
        $this->assertSame('', $sut->getFolderId());
        $this->assertSame('', $sut->getFolderName());
        $this->assertSame([], $sut->getAltTexts());
    }

    #[DataProvider('isDirectoryDataProvider')]
    public function testIsDirectory(string $fileType, bool $expectedResult): void
    {
        $sut = new Media(
            oxid: 'someOxid',
            fileName: 'filename.jpg',
            fileSize: 25,
            fileType: $fileType,
            imageSize: $this->createStub(ImageSize::class),
            folderId: 'someFolderId'
        );

        $this->assertSame($expectedResult, $sut->isDirectory());
    }

    public static function isDirectoryDataProvider(): \Generator
    {
        yield "some gif image filetype" => ['fileType' => 'image/gif', 'expectedResult' => false];
        yield "some jpeg image filetype" => ['fileType' => 'image/jpeg', 'expectedResult' => false];
        yield "directory media type" => ['fileType' => Media::FILETYPE_DIRECTORY, 'expectedResult' => true];
    }

    public function testAltTextsWithValidTranslations(): void
    {
        $altTexts = [
            'de_DE' => 'German Alt Text',
            'en_EN' => 'English Alt Text',
            'fr_FR' => 'French Alt Text',
        ];

        $sut = new Media(
            oxid: 'someOxid',
            fileName: 'filename.jpg',
            altTexts: $altTexts
        );

        $this->assertSame($altTexts, $sut->getAltTexts());
        $this->assertIsArray($sut->getAltTexts());
        
        // Verify array structure
        foreach ($sut->getAltTexts() as $locale => $text) {
            $this->assertIsString($locale);
            $this->assertIsString($text);
            $this->assertNotEmpty($locale);
        }
    }

    public function testAltTextsWithEmptyArray(): void
    {
        $sut = new Media(
            oxid: 'someOxid',
            fileName: 'filename.jpg',
            altTexts: []
        );

        $this->assertSame([], $sut->getAltTexts());
        $this->assertIsArray($sut->getAltTexts());
        $this->assertEmpty($sut->getAltTexts());
    }

    public function testAltTextsWithEmptyStrings(): void
    {
        $altTexts = [
            'de_DE' => '',
            'en_EN' => 'Valid Text',
        ];

        $sut = new Media(
            oxid: 'someOxid',
            fileName: 'filename.jpg',
            altTexts: $altTexts
        );

        $this->assertSame($altTexts, $sut->getAltTexts());
        $this->assertIsArray($sut->getAltTexts());
        
        // Even empty strings should be allowed as values
        $this->assertSame('', $sut->getAltTexts()['de_DE']);
        $this->assertSame('Valid Text', $sut->getAltTexts()['en_EN']);
    }

    /**
     * Test that altTexts maintains associative array structure
     */
    public function testAltTextsIsAssociativeArray(): void
    {
        $altTexts = [
            'locale1' => 'Text 1',
            'locale2' => 'Text 2',
            'locale3' => 'Text 3',
        ];

        $sut = new Media(
            oxid: 'someOxid',
            fileName: 'filename.jpg',
            altTexts: $altTexts
        );

        $result = $sut->getAltTexts();
        
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        
        // Verify it's associative (not numeric indexed)
        $this->assertTrue(array_keys($result) !== range(0, count($result) - 1));
        
        // Verify all keys are strings
        foreach (array_keys($result) as $key) {
            $this->assertIsString($key);
        }
        
        // Verify all values are strings
        foreach ($result as $value) {
            $this->assertIsString($value);
        }
    }

    public function testAltTextsDefaultsToEmptyArray(): void
    {
        $sut = new Media(
            oxid: 'someOxid',
            fileName: 'filename.jpg'
            // altTexts parameter omitted, should default to []
        );

        $this->assertSame([], $sut->getAltTexts());
        $this->assertIsArray($sut->getAltTexts());
        $this->assertEmpty($sut->getAltTexts());
    }
}
