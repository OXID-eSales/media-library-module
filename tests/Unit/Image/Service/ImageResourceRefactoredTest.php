<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace Image\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactored;
use OxidEsales\MediaLibrary\Media\DataType\Media;
use OxidEsales\MediaLibrary\Service\NamingServiceInterface;
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
        NamingServiceInterface $namingService = null,
    ) {
        return new ImageResourceRefactored(
            shopConfig: $shopConfig ?? $this->createStub(Config::class),
            namingService: $namingService ?? $this->createStub(NamingServiceInterface::class),
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

    public function testGetPathToMediaFile(): void
    {
        $mediaFileName = uniqid();
        $directoryName = uniqid();
        $exampleMedia = new Media(
            oxid: uniqid(),
            fileName: $mediaFileName,
            fileType: uniqid(),
            folderName: $directoryName
        );

        $examplePath = 'examplePathWithConcreteDirectory';
        $sut = $this->createPartialMock(ImageResourceRefactored::class, ['getPathToMediaFiles']);
        $sut->method('getPathToMediaFiles')->with($directoryName)->willReturn($examplePath);

        $expectedPath = $examplePath . '/' . $mediaFileName;
        $this->assertSame($expectedPath, $sut->getPathToMediaFile($exampleMedia));
    }

    public function testCalculateUniqueFilePath(): void
    {
        $fileName = uniqid();
        $folderName = uniqid();

        $sut = $this->getMockBuilder(ImageResourceRefactored::class)
            ->setConstructorArgs([
                'shopConfig' => $this->createStub(Config::class),
                'namingService' => $namingService = $this->createMock(NamingServiceInterface::class),
            ])
            ->onlyMethods(['getPathToMediaFiles'])
            ->getMock();
        $sut->method('getPathToMediaFiles')->with($folderName)->willReturn('someMediaDirectory');

        $somePath = "someMediaDirectory/{$fileName}";
        $someUniquePath = 'someDirectory/someFileName';
        $namingService->method('getUniqueFilename')->with($somePath)->willReturn($someUniquePath);

        $result = $sut->getPossibleMediaFilePath($folderName, $fileName);

        $this->assertSame($someUniquePath, $result->getPath());
        $this->assertSame('someFileName', $result->getFileName());
    }
}
