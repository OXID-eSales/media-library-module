<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace Image\ThumbnailGenerator;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\MediaLibrary\Image\ThumbnailGenerator\SvgDriver;
use OxidEsales\MediaLibrary\Service\FileSystemServiceInterface;
use OxidEsales\MediaLibrary\Tests\Integration\IntegrationTestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Image\ThumbnailGenerator\SvgDriver
 */
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
            size: $this->createStub(ImageSizeInterface::class),
            blCrop: (bool)random_int(0, 1)
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

    public function getSut(
        FileSystemServiceInterface $fileSystemService = null
    ): SvgDriver {
        return new SvgDriver(
            fileSystemService: $fileSystemService ?? $this->createStub(FileSystemServiceInterface::class)
        );
    }
}
