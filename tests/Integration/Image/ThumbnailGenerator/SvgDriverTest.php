<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace Image\ThumbnailGenerator;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\MediaLibrary\Image\ThumbnailGenerator\DefaultDriver;
use OxidEsales\MediaLibrary\Image\ThumbnailGenerator\SvgDriver;
use OxidEsales\MediaLibrary\Service\FileSystemServiceInterface;
use OxidEsales\MediaLibrary\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(SvgDriver::class)]
class SvgDriverTest extends IntegrationTestCase
{
    public function testGenerateThumbnail(): void
    {
        $sut = $this->getSut(
            fileSystemService: $fileSystemSpy = $this->createMock(FileSystemServiceInterface::class)
        );

        $filePath = uniqid();
        $thumbPath = uniqid();

        $fileSystemSpy->expects($this->once())->method('copy')->with($filePath, $thumbPath);

        $sut->generateThumbnail(
            sourcePath: $filePath,
            thumbnailPath: $thumbPath,
            thumbnailSize: $this->createStub(ImageSizeInterface::class),
            isCropRequired: (bool)random_int(0, 1)
        );
    }

    public function testIsOriginSupported(): void
    {
        $sut = $this->getSut();
        $this->assertTrue($sut->isOriginSupported('xxx/someSvgPath.svg'));
        $this->assertTrue($sut->isOriginSupported('xxx/someSvgPath.SVG'));
        $this->assertFalse($sut->isOriginSupported('yyy/someOther.doc'));
        $this->assertFalse($sut->isOriginSupported('yyy/someOther.gif'));
        $this->assertFalse($sut->isOriginSupported('yyy/someOther.jpg'));
    }

    #[DataProvider('getThumbnailFileNameDataProvider')]
    public function testGetThumbnailFileName(
        string $originalFileName,
        string $expectedName
    ): void {
        $sut = $this->getSut();

        $result = $sut->getThumbnailFileName(
            originalFileName: $originalFileName,
            thumbnailSize: $this->createStub(ImageSizeInterface::class),
            isCropRequired: (bool)random_int(0, 1)
        );

        $this->assertSame($expectedName, $result);
    }

    public static function getThumbnailFileNameDataProvider(): \Generator
    {
        $fileName = 'SomeFileName.SVG';
        $fileNameHash = md5($fileName);

        yield "regular svg" => [
            'originalFileName' => $fileName,
            'expectedName' => $fileNameHash . '.svg'
        ];
    }

    public function testGetThumbnailsGlob(): void
    {
        $sut = $this->getSut();

        $originalFilename = 'someExampleFilename.svg';
        $this->assertSame('5a1040df467f3ceae2623aa5918f542a.svg', $sut->getThumbnailsGlob($originalFilename));
    }

    public function getSut(
        FileSystemServiceInterface $fileSystemService = null
    ): SvgDriver {
        return new SvgDriver(
            fileSystemService: $fileSystemService ?? $this->createStub(FileSystemServiceInterface::class)
        );
    }
}
