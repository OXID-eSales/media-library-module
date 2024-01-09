<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace Image\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceInterface;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactored;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactored
 */
class ImageResourceRefactoredTest extends TestCase
{
    public function testGetPathToMediaFiles(): void
    {
        $sut = $this->getSut(
            shopConfig: $shopConfigStub = $this->createStub(Config::class)
        );
        $shopConfigStub->method('getConfigParam')->with('sShopDir')->willReturn('someShopDir');

        $this->assertSame('someShopDir/' . ImageResourceRefactored::MEDIA_PATH, $sut->getPathToMediaFiles());
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

    protected function getSut(
        Config $shopConfig = null,
        ImageResourceInterface $oldImageResource = null,
    ) {
        return new ImageResourceRefactored(
            shopConfig: $shopConfig ?: $this->createStub(Config::class),
            oldImageResource: $oldImageResource ?: $this->createStub(ImageResourceInterface::class),
        );
    }
}
