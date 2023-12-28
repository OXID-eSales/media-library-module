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
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Media\DataType\Media
 */
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
            thumbFileName: 'thumbfilename.jpg',
            imageSize: $imageSize,
            folderId: 'someFolderId'
        );

        $this->assertSame('someOxid', $sut->getOxid());
        $this->assertSame('filename.jpg', $sut->getFileName());
        $this->assertSame(25, $sut->getFileSize());
        $this->assertSame('image/gif', $sut->getFileType());
        $this->assertSame('thumbfilename.jpg', $sut->getThumbFileName());
        $this->assertSame($imageSize, $sut->getImageSize());
        $this->assertSame('someFolderId', $sut->getFolderId());
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
        $this->assertSame('', $sut->getThumbFileName());
        $this->assertEquals(new ImageSize(0, 0), $sut->getImageSize());
        $this->assertSame('', $sut->getFolderId());
    }

    /** @dataProvider isDirectoryDataProvider */
    public function testIsDirectory(string $fileType, bool $expectedResult): void
    {
        $sut = new Media(
            oxid: 'someOxid',
            fileName: 'filename.jpg',
            fileSize: 25,
            fileType: $fileType,
            thumbFileName: 'thumbfilename.jpg',
            imageSize: $this->createStub(ImageSize::class),
            folderId: 'someFolderId'
        );

        $this->assertSame($expectedResult, $sut->isDirectory());
    }

    public function isDirectoryDataProvider(): \Generator
    {
        yield "some gif image filetype" => ['fileType' => 'image/gif', 'expectedResult' => false];
        yield "some jpeg image filetype" => ['fileType' => 'image/jpeg', 'expectedResult' => false];
        yield "directory media type" => ['fileType' => Media::FILETYPE_DIRECTORY, 'expectedResult' => true];
    }

    public function testGetFrontendObject(): void
    {
        $imageSize = new ImageSize(100, 100);

        $sut = new Media(
            oxid: 'someOxid',
            fileName: 'filename.jpg',
            fileSize: 25,
            fileType: 'image/gif',
            thumbFileName: 'thumbfilename.jpg',
            imageSize: $imageSize,
            folderId: 'someFolderId'
        );

        $expected = new FrontendMedia(
            id: 'someOxid',
            file: 'filename.jpg',
            filetype: 'image/gif',
            filesize: 25,
            thumb: 'thumbfilename.jpg',
            imageSize: '100x100'
        );

        $this->assertEquals($expected, $sut->getFrontendData());
    }
}
