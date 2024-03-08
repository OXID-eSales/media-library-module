<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Unit\Image\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\MediaLibrary\Media\DataType\Media;
use OxidEsales\MediaLibrary\Media\Service\MediaResource;
use OxidEsales\MediaLibrary\Service\ModuleSettingsInterface;
use OxidEsales\MediaLibrary\Service\NamingServiceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Media\Service\MediaResource
 */
class ImageResourceTest extends TestCase
{
    protected const EXAMPLE_SHOP_URL = 'someShopUrl';

    public function testGetPathToMediaFiles(): void
    {
        $sut = $this->getSut(
            shopConfig: $shopConfigStub = $this->createStub(Config::class)
        );
        $shopConfigStub->method('getConfigParam')->with('sShopDir')->willReturn('someShopDir');

        $this->assertSame('someShopDir/' . MediaResource::MEDIA_PATH, $sut->getPathToMediaFiles());
    }

    protected function getSut(
        Config $shopConfig = null,
        NamingServiceInterface $namingService = null,
        ModuleSettingsInterface $moduleSettings = null,
    ) {
        return new MediaResource(
            shopConfig: $shopConfig ?? $this->createStub(Config::class),
            namingService: $namingService ?? $this->createStub(NamingServiceInterface::class),
            moduleSettings: $moduleSettings ?? $this->createStub(ModuleSettingsInterface::class),
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
            'someShopDir/' . MediaResource::MEDIA_PATH . $subDirectory,
            $sut->getPathToMediaFiles($subDirectory)
        );
    }

    public static function getUrlToMediaDataProvider(): \Generator
    {
        yield "no folder no filename" => [
            'folder' => '',
            'fileName' => '',
            'expectedResult' => self::EXAMPLE_SHOP_URL . '/' . MediaResource::MEDIA_PATH
        ];

        yield "some folder no filename" => [
            'folder' => 'some',
            'fileName' => '',
            'expectedResult' => self::EXAMPLE_SHOP_URL . '/' . MediaResource::MEDIA_PATH . '/some'
        ];

        yield "some folder other filename" => [
            'folder' => 'some',
            'fileName' => 'other.xx',
            'expectedResult' => self::EXAMPLE_SHOP_URL . '/' . MediaResource::MEDIA_PATH . '/some/other.xx'
        ];

        yield "no folder other filename" => [
            'folder' => '',
            'fileName' => 'other.xx',
            'expectedResult' => self::EXAMPLE_SHOP_URL . '/' . MediaResource::MEDIA_PATH . '/other.xx'
        ];
    }

    /** @dataProvider getUrlToMediaDataProvider */
    public function testGetUrlToMediaFile(
        string $folder,
        string $fileName,
        string $expectedResult
    ): void {
        $sut = $this->getSut(
            shopConfig: $shopConfigStub = $this->createStub(Config::class)
        );
        $shopConfigStub->method('getSslShopUrl')->willReturn(self::EXAMPLE_SHOP_URL);

        $this->assertSame($expectedResult, $sut->getUrlToMediaFile($folder, $fileName));
    }

    public static function getUrlToMediaWithAlternativeUrlSetDataProvider(): \Generator
    {
        yield "no folder no filename" => [
            'folder' => '',
            'fileName' => '',
            'alternativeUrl' => 'someAlternativeUrl1',
            'expectedResult' => 'someAlternativeUrl1'
        ];

        yield "some folder no filename" => [
            'folder' => 'some',
            'fileName' => '',
            'alternativeUrl' => 'someAlternativeUrl2',
            'expectedResult' => 'someAlternativeUrl2/some'
        ];

        yield "some folder other filename" => [
            'folder' => 'some',
            'fileName' => 'other.xx',
            'alternativeUrl' => 'someAlternativeUrl3',
            'expectedResult' => 'someAlternativeUrl3/some/other.xx'
        ];

        yield "no folder other filename" => [
            'folder' => '',
            'fileName' => 'other.xx',
            'alternativeUrl' => 'someAlternativeUrl4',
            'expectedResult' => 'someAlternativeUrl4/other.xx'
        ];
    }

    /** @dataProvider getUrlToMediaWithAlternativeUrlSetDataProvider */
    public function testGetUrlToMediaFileWithAlternativeUrlSet(
        string $folder,
        string $fileName,
        string $alternativeUrl,
        string $expectedResult
    ): void {
        $sut = $this->getSut(
            moduleSettings: $moduleSettings = $this->createStub(ModuleSettingsInterface::class),
        );
        $moduleSettings->method('getAlternativeImageUrl')->willReturn($alternativeUrl);

        $this->assertSame($expectedResult, $sut->getUrlToMediaFile($folder, $fileName));
    }

    public function testGetUrlToMediaFiles(): void
    {
        $sut = $this->getSut(
            shopConfig: $shopConfigStub = $this->createStub(Config::class)
        );
        $shopConfigStub->method('getSslShopUrl')->willReturn('someShopUrl');

        $this->assertSame('someShopUrl/' . MediaResource::MEDIA_PATH, $sut->getUrlToMediaFiles());
    }

    public function testGetUrlToMediaFilesWithFolder(): void
    {
        $sut = $this->getSut(
            shopConfig: $shopConfigStub = $this->createStub(Config::class)
        );
        $shopConfigStub->method('getSslShopUrl')->willReturn('someShopUrl');

        $this->assertSame(
            'someShopUrl/' . MediaResource::MEDIA_PATH . '/someFolder',
            $sut->getUrlToMediaFiles('someFolder')
        );
    }

    public function testGetUrlToMediaFilesWithAlternativeUrl(): void
    {
        $sut = $this->getSut(
            moduleSettings: $moduleSettings = $this->createStub(ModuleSettingsInterface::class),
        );
        $alternativeUrl = 'someAlternativeUrl';
        $moduleSettings->method('getAlternativeImageUrl')->willReturn($alternativeUrl);

        $this->assertSame($alternativeUrl, $sut->getUrlToMediaFiles());
    }

    public function testGetUrlToMediaFilesWithAlternativeUrlAndSpecificFolder(): void
    {
        $sut = $this->getSut(
            moduleSettings: $moduleSettings = $this->createStub(ModuleSettingsInterface::class),
        );
        $alternativeUrl = 'someAlternativeUrl';
        $moduleSettings->method('getAlternativeImageUrl')->willReturn($alternativeUrl);

        $this->assertSame($alternativeUrl . '/someFolder', $sut->getUrlToMediaFiles('someFolder'));
    }

    public function testGetPathToMedia(): void
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
        $sut = $this->createPartialMock(MediaResource::class, ['getPathToMediaFiles']);
        $sut->method('getPathToMediaFiles')->with($directoryName)->willReturn($examplePath);

        $expectedPath = $examplePath . '/' . $mediaFileName;
        $this->assertSame($expectedPath, $sut->getPathToMedia($exampleMedia));
    }

    public function testGetPathToMediaFile(): void
    {
        $mediaFileName = uniqid();
        $directoryName = uniqid();

        $examplePath = 'examplePathWithConcreteDirectory';
        $sut = $this->createPartialMock(MediaResource::class, ['getPathToMediaFiles']);
        $sut->method('getPathToMediaFiles')->with($directoryName)->willReturn($examplePath);

        $expectedPath = $examplePath . '/' . $mediaFileName;
        $this->assertSame($expectedPath, $sut->getPathToMediaFile($directoryName, $mediaFileName));
    }

    public function testCalculateUniqueFilePath(): void
    {
        $fileName = uniqid();
        $folderName = uniqid();

        $sut = $this->getMockBuilder(MediaResource::class)
            ->setConstructorArgs([
                'shopConfig' => $this->createStub(Config::class),
                'namingService' => $namingService = $this->createMock(NamingServiceInterface::class),
                'moduleSettings' => $this->createStub(ModuleSettingsInterface::class),
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
