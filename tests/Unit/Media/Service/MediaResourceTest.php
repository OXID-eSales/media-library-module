<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\MediaLibrary\Media\Service\MediaResource;
use OxidEsales\MediaLibrary\Service\NamingServiceInterface;
use OxidEsales\MediaLibrary\Settings\Service\ModuleSettingsInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MediaResource::class)]
class MediaResourceTest extends TestCase
{
    protected const EXAMPLE_SHOP_URL = 'someShopUrl';

    #[Test]
    public function getPathToMediaFiles(): void
    {
        $sut = $this->getSut(
            shopConfig: $shopConfigStub = $this->createStub(Config::class)
        );
        $shopConfigStub->method('getConfigParam')->with('sShopDir')->willReturn('someShopDir');

        $this->assertSame('someShopDir/' . MediaResource::MEDIA_PATH, $sut->getPathToMediaFiles());
    }

    #[Test]
    public function getPathToMediaFilesWithSubdirectory(): void
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

    #[Test]
    #[DataProvider('getUrlToMediaDataProvider')]
    public function getUrlToMediaFile(
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

    #[Test]
    #[DataProvider('getUrlToMediaWithAlternativeUrlSetDataProvider')]
    public function getUrlToMediaFileWithAlternativeUrlSet(
        string $folder,
        string $fileName,
        string $alternativeUrl,
        string $expectedResult
    ): void {
        $sut = $this->getSut(
            moduleSettings: $moduleSettings = $this->createStub(
                ModuleSettingsInterface::class
            ),
        );
        $moduleSettings->method('getAlternativeImageUrl')->willReturn($alternativeUrl);

        $this->assertSame($expectedResult, $sut->getUrlToMediaFile($folder, $fileName));
    }

    #[Test]
    public function getUrlToMediaFiles(): void
    {
        $sut = $this->getSut(
            shopConfig: $shopConfigStub = $this->createStub(Config::class)
        );
        $shopConfigStub->method('getSslShopUrl')->willReturn('someShopUrl');

        $this->assertSame('someShopUrl/' . MediaResource::MEDIA_PATH, $sut->getUrlToMediaFiles());
    }

    #[Test]
    public function getUrlToMediaFilesWithFolder(): void
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

    #[Test]
    public function getUrlToMediaFilesWithAlternativeUrl(): void
    {
        $sut = $this->getSut(
            moduleSettings: $moduleSettings = $this->createStub(
                ModuleSettingsInterface::class
            ),
        );
        $alternativeUrl = 'someAlternativeUrl';
        $moduleSettings->method('getAlternativeImageUrl')->willReturn($alternativeUrl);

        $this->assertSame($alternativeUrl, $sut->getUrlToMediaFiles());
    }

    #[Test]
    public function getUrlToMediaFilesWithAlternativeUrlAndSpecificFolder(): void
    {
        $sut = $this->getSut(
            moduleSettings: $moduleSettings = $this->createStub(
                ModuleSettingsInterface::class
            ),
        );
        $alternativeUrl = 'someAlternativeUrl';
        $moduleSettings->method('getAlternativeImageUrl')->willReturn($alternativeUrl);

        $this->assertSame($alternativeUrl . '/someFolder', $sut->getUrlToMediaFiles('someFolder'));
    }

    #[Test]
    public function getPathToMediaFile(): void
    {
        $mediaFileName = uniqid();
        $directoryName = uniqid();

        $examplePath = 'examplePathWithConcreteDirectory';
        $sut = $this->createPartialMock(MediaResource::class, ['getPathToMediaFiles']);
        $sut->method('getPathToMediaFiles')->with($directoryName)->willReturn($examplePath);

        $expectedPath = $examplePath . '/' . $mediaFileName;
        $this->assertSame($expectedPath, $sut->getPathToMediaFile($directoryName, $mediaFileName));
    }

    #[Test]
    public function getPossibleMediaFilePath(): void
    {
        $shopDir = '/var/www/source';
        $mediaRoot = $shopDir . '/' . MediaResource::MEDIA_PATH;
        $folderName = uniqid();
        $fileName = uniqid() . '.svg';
        $expectedJoin = "{$mediaRoot}/{$folderName}/{$fileName}";
        $uniqueBaseName = uniqid() . '.svg';
        $uniquePath = "{$mediaRoot}/{$folderName}/{$uniqueBaseName}";

        $shopConfigMock = $this->createMock(Config::class);
        $shopConfigMock->method('getConfigParam')->with('sShopDir')->willReturn($shopDir);

        $namingServiceMock = $this->createMock(NamingServiceInterface::class);
        $namingServiceMock->method('getUniqueFilename')
            ->with($expectedJoin)
            ->willReturn($uniquePath);

        $sut = $this->getSut(shopConfig: $shopConfigMock, namingService: $namingServiceMock);

        $result = $sut->getPossibleMediaFilePath($folderName, $fileName);

        $this->assertSame($uniquePath, $result->getPath());
        $this->assertSame($uniqueBaseName, $result->getFileName());
    }

    public static function traversalFileNameProvider(): \Generator
    {
        yield "escapes within shop root" => ['fileName' => '../../../pt-B.svg'];
        yield "escapes above shop root"  => ['fileName' => 'a/../../../../../pt-D.svg'];
    }

    #[Test]
    #[DataProvider('traversalFileNameProvider')]
    public function getPossibleMediaFilePathRejectsTraversalFileNames(string $fileName): void
    {
        $shopConfigMock = $this->createMock(Config::class);
        $shopConfigMock->method('getConfigParam')->with('sShopDir')->willReturn('/var/www/source');

        $namingServiceStub = $this->createStub(NamingServiceInterface::class);
        $namingServiceStub->method('getUniqueFilename')->willReturnArgument(0);

        $sut = $this->getSut(shopConfig: $shopConfigMock, namingService: $namingServiceStub);

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('OE_MEDIA_LIBRARY_EXCEPTION_FILENAME_INVALID_PATH');

        $sut->getPossibleMediaFilePath('', $fileName);
    }

    protected function getSut(
        Config $shopConfig = null,
        NamingServiceInterface $namingService = null,
        ModuleSettingsInterface $moduleSettings = null,
    ): MediaResource {
        return new MediaResource(
            shopConfig: $shopConfig ?? $this->createStub(Config::class),
            namingService: $namingService ?? $this->createStub(NamingServiceInterface::class),
            moduleSettings: $moduleSettings ?? $this->createStub(
                ModuleSettingsInterface::class
            ),
        );
    }
}
