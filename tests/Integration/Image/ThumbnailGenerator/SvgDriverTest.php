<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Integration\Image\ThumbnailGenerator;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\MediaLibrary\Image\ThumbnailGenerator\SvgDriver;
use OxidEsales\MediaLibrary\Image\ThumbnailGenerator\ThumbnailGeneratorInterface;
use OxidEsales\MediaLibrary\Service\FileSystemServiceInterface;
use OxidEsales\MediaLibrary\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(SvgDriver::class)]
class SvgDriverTest extends IntegrationTestCase
{
    #[Test]
    public function generateThumbnail(): void
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

    #[Test]
    public function isOriginSupported(): void
    {
        $sut = $this->getSut();
        $this->assertTrue($sut->isOriginSupported('xxx/someSvgPath.svg'));
        $this->assertTrue($sut->isOriginSupported('xxx/someSvgPath.SVG'));
        $this->assertFalse($sut->isOriginSupported('yyy/someOther.doc'));
        $this->assertFalse($sut->isOriginSupported('yyy/someOther.gif'));
        $this->assertFalse($sut->isOriginSupported('yyy/someOther.jpg'));
    }

    #[DataProvider('getThumbnailFileNameDataProvider')]
    #[Test]
    public function getThumbnailFileName(
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

    #[Test]
    public function getThumbnailsGlob(): void
    {
        $sut = $this->getSut();

        $originalFilename = 'someExampleFilename.svg';
        $this->assertSame('5a1040df467f3ceae2623aa5918f542a.svg', $sut->getThumbnailsGlob($originalFilename));
    }

    private function getSut(
        FileSystemServiceInterface $fileSystemService = null
    ): ThumbnailGeneratorInterface {
        return new SvgDriver(
            fileSystemService: $fileSystemService ?? $this->createStub(FileSystemServiceInterface::class)
        );
    }
}
