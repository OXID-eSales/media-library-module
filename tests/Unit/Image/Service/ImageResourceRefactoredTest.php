<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace Image\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactored;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactored
 */
class ImageResourceRefactoredTest extends TestCase
{
    protected const EXAMPLE_SHOP_URL = 'someShopUrl';

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
    ) {
        return new ImageResourceRefactored(
            shopConfig: $shopConfig ?: $this->createStub(Config::class),
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

    public static function getUrlToMediaDataProvider(): \Generator
    {
        yield "no folder no filename" => [
            'folder' => '',
            'fileName' => '',
            'expectedResult' => self::EXAMPLE_SHOP_URL . '/' . ImageResourceRefactored::MEDIA_PATH
        ];

        yield "some folder no filename" => [
            'folder' => 'some',
            'fileName' => '',
            'expectedResult' => self::EXAMPLE_SHOP_URL . '/' . ImageResourceRefactored::MEDIA_PATH . '/some'
        ];

        yield "some folder other filename" => [
            'folder' => 'some',
            'fileName' => 'other.xx',
            'expectedResult' => self::EXAMPLE_SHOP_URL . '/' . ImageResourceRefactored::MEDIA_PATH . '/some/other.xx'
        ];

        yield "no folder other filename" => [
            'folder' => '',
            'fileName' => 'other.xx',
            'expectedResult' => self::EXAMPLE_SHOP_URL . '/' . ImageResourceRefactored::MEDIA_PATH . '/other.xx'
        ];
    }

    /** @dataProvider getUrlToMediaDataProvider */
    public function testGetUrlToMedia(
        string $folder,
        string $fileName,
        string $expectedResult
    ): void {
        $sut = $this->getSut(
            shopConfig: $shopConfigStub = $this->createStub(Config::class)
        );
        $shopConfigStub->method('getSslShopUrl')->willReturn(self::EXAMPLE_SHOP_URL);

        $this->assertSame($expectedResult, $sut->getUrlToMedia($folder, $fileName));
    }
}
