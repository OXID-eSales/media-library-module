<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Unit\Image\Service;

use org\bovigo\vfs\vfsStream;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\MediaLibrary\Image\Service\ImageResource;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailGeneratorInterface;
use OxidEsales\MediaLibrary\Service\ModuleSettings;
use PHPUnit\Framework\TestCase;

class ImageResourceTest extends TestCase
{
    private const FIXTURE_FILE = 'file.jpg';
    private const FIXTURE_FOLDER = 'some_folder';

    public function testGetDefaultThumbnailSize(): void
    {
        $sut = $this->getSut();
        $resultActual = $sut->getDefaultThumbnailSize();
        self::assertEquals(185, $resultActual);
    }
    public function testGetThumbTitleWithSize(): void
    {
        $sut = $this->getSut();
        $sFile = 'Auto_02b_Hinten.jpg';
        $expected = "afe4bbfe0475985f7f54e0267a35edfe_thumb_185*185.jpg";

        $imageSize = new ImageSize(185, 185);
        $resultActual = $sut->getThumbName($sFile, $imageSize);

        self::assertEquals($expected, $resultActual);
    }

    /**
     * @dataProvider getImagePathDataProvider
     */
    public function testGetMediaPathNoAlternativeUrl($file)
    {
        $moduleSettingsMock = $this->createConfiguredMock(
            ModuleSettings::class,
            [
                'getAlternativeImageDirectory' => '',
            ]
        );

        $shopConfigMock = $this->createPartialMock(Config::class, ['getConfigParam']);
        $shopConfigMock->expects($this->any())
            ->method('getConfigParam')
            ->with('sShopDir')
            ->willReturn('someShopDir/');

        $sut = $this->getSut(
            shopConfig: $shopConfigMock,
            moduleSettings: $moduleSettingsMock,
        );
        $mediaPath = $sut->getMediaPath($file);

        $this->assertSame('someShopDir' . ImageResource::MEDIA_PATH . $file, $mediaPath);
    }

    /**
     * @dataProvider getImagePathDataProvider
     */
    public function testGetMediaPathWithAlternativeUrl($file)
    {
        $externalUrl = 'https://some-cdn-url.com';
        $moduleSettingsMock = $this->createConfiguredMock(
            ModuleSettings::class,
            [
                'getAlternativeImageDirectory' => $externalUrl,
            ]
        );

        $sut = $this->getSut(
            moduleSettings: $moduleSettingsMock,
        );
        $mediaPath = $sut->getMediaPath($file);

        $this->assertSame($externalUrl . ImageResource::MEDIA_PATH_SHORT . $file, $mediaPath);
    }

    /**
     * @dataProvider getImagePathDataProvider
     */
    public function testGetMediaUrlNoAlternativeUrl($file)
    {
        $externalUrl = 'https://some-cdn-url.com';
        $aFilepath = explode('/', $file);
        if (count($aFilepath) > 1) {
            $filename = [
                $aFilepath[0] => [
                    $aFilepath[1] => 'file content',
                ],
            ];
        } else {
            $filename = [
                $aFilepath[0] => 'file content',
            ];
        }
        $structure = [
            'out' => [
                'pictures' => [
                    'ddmedia' => $filename,
                ],
            ],
        ];
        $directory = vfsStream::setup('root', 0777, $structure);

        $moduleSettingsMock = $this->createConfiguredMock(
            ModuleSettings::class,
            [
                'getAlternativeImageDirectory' => '',
            ]
        );

        $shopConfigMock = $this->createPartialMock(Config::class, ['getConfigParam', 'getSslShopUrl']);
        $shopConfigMock->expects($this->any())
            ->method('getConfigParam')
            ->willReturnMap(
                [
                    ['sShopDir', null, $directory->url()],
                ]
            );
        $shopConfigMock->expects($this->any())
            ->method('getSslShopUrl')
            ->willReturn($externalUrl);

        $sut = $this->getSut(
            shopConfig: $shopConfigMock,
            moduleSettings: $moduleSettingsMock,
        );

        $this->assertSame($externalUrl . ImageResource::MEDIA_PATH . $file, $sut->getMediaUrl($file));
    }

    /**
     * @dataProvider getImagePathDataProvider
     */
    public function testGetMediaUrlNotExistingFile($file)
    {
        $moduleSettingsMock = $this->createConfiguredMock(
            ModuleSettings::class,
            [
                'getAlternativeImageDirectory' => '',
            ]
        );

        $shopConfigMock = $this->createPartialMock(Config::class, ['getConfigParam']);
        $shopConfigMock->expects($this->any())
            ->method('getConfigParam')
            ->willReturnMap(
                [
                    ['sShopDir', null, ''],
                ]
            );

        $sut = $this->getSut(
            shopConfig: $shopConfigMock,
            moduleSettings: $moduleSettingsMock,
        );

        $this->assertSame(false, $sut->getMediaUrl($file));
    }

    /**
     * @dataProvider getImagePathDataProvider
     */
    public function testGetMediaUrlWithAlternativeUrl($file)
    {
        $externalUrl = 'https://some-cdn-url.com';
        $moduleSettingsMock = $this->createConfiguredMock(
            ModuleSettings::class,
            [
                'getAlternativeImageDirectory' => $externalUrl,
            ]
        );

        $sut = $this->getSut(null, $moduleSettingsMock);
        $mediaPath = $sut->getMediaUrl($file);

        $this->assertSame($externalUrl . ImageResource::MEDIA_PATH_SHORT . $file, $mediaPath);
    }

    /**
     * @dataProvider getThumbnailPathDataProvider
     */
    public function testGetThumbnailPath($file, $expectedPath)
    {
        $path = "somePath";
        $sut = $this->createPartialMock(imageResource::class, ["getMediaPath"]);
        $sut->expects($this->any())->method("getMediaPath")->willReturn($path);

        $this->assertSame($expectedPath, $sut->getThumbnailPath($file));
    }

    public function testGetMediaUrl()
    {
        $sut = $this->getSut();
        $sThumbName = $this->getImageSizeAsString(
            md5(self::FIXTURE_FILE) . '_thumb_',
            $sut->getDefaultThumbnailSize()
        );
        $structure['out']['pictures']['ddmedia'][self::FIXTURE_FILE] = 'some file';
        $structure['out']['pictures']['ddmedia']['thumbs'][$sThumbName] = 'some file';
        $anotherFile = 'file2.jpg';
        $sThumbName = $this->getImageSizeAsString(
            md5($anotherFile) . '_thumb_',
            $sut->getDefaultThumbnailSize()
        );
        $structure['out']['pictures']['ddmedia'][self::FIXTURE_FOLDER][$anotherFile] = 'some file';
        $structure['out']['pictures']['ddmedia'][self::FIXTURE_FOLDER]['thumbs'][$sThumbName] = 'some file';

        $directory = vfsStream::setup('root', 0777, $structure);

        $shopConfigMock = $this->createPartialMock(Config::class, ['getConfigParam']);
        $shopConfigMock->expects($this->any())
            ->method('getConfigParam')
            ->willReturnMap(
                [
                    ['sShopDir', null, $directory->url()],
                    ['sSSLShopURL', null, 'https://test.com'],
                ]
            );
        $sut = $this->getSut($shopConfigMock);

        $sMediaUrl = $sut->getMediaUrl(self::FIXTURE_FOLDER . '/' . $anotherFile);
        $this->assertEquals(
            'https://test.com/out/pictures/ddmedia/' . self::FIXTURE_FOLDER . '/' . $anotherFile,
            $sMediaUrl
        );

        $sMediaUrl = $sut->getMediaUrl(self::FIXTURE_FILE);
        $this->assertEquals(
            'https://test.com/out/pictures/ddmedia/' . self::FIXTURE_FILE,
            $sMediaUrl
        );
    }

    /**
     * @dataProvider getThumbnailUrlProvider
     *
     * @return void
     */
    public function testGetThumbnailUrl($sFile, ?ImageSizeInterface $imageSize = null, $expected)
    {
        $sThumbName = $this->getImageSizeAsString(
            md5(self::FIXTURE_FILE) . '_thumb_',
            $this->getSut()->getDefaultThumbnailSize()
        );
        $structure['out']['pictures']['ddmedia']['thumbs'] = [];
        $structure['out']['pictures']['ddmedia'][self::FIXTURE_FILE] = 'some file';
        $structure['out']['pictures']['ddmedia']['thumbs'][$sThumbName] = 'some file';
        $directory = vfsStream::setup('root', 0777, $structure);
        $shopConfigMock = $this->createPartialMock(Config::class, ['getConfigParam']);
        $shopConfigMock->expects($this->any())
            ->method('getConfigParam')
            ->willReturnMap(
                [
                    ['sShopDir', null, $directory->url()],
                    ['sSSLShopURL', null, 'https://test.com'],
                ]
            );
        $sut = $this->getSut($shopConfigMock);

        $size = $sut->getDefaultThumbnailSize();
        $imageSize = new ImageSize($size, $size);
        $result = $sut->getThumbnailUrl($sFile, $imageSize);

        $this->assertEquals($expected ? ('https://test.com/out/pictures/ddmedia/' . $expected) : $expected, $result);
    }

    public function getImagePathDataProvider(): array
    {
        return [
            [
                'file' => self::FIXTURE_FILE,
            ],
            [
                'file' => self::FIXTURE_FOLDER . '/' . self::FIXTURE_FILE,
            ],
        ];
    }

    public function getThumbnailPathDataProvider(): array
    {
        return [
            [
                'file'         => 'somefile.jpg',
                'expectedPath' => 'somePath/thumbs/somefile.jpg',
            ],
            [
                'file'         => '',
                'expectedPath' => 'somePath/thumbs',
            ],
        ];
    }

    public function getThumbnailUrlProvider()
    {
        $sut = $this->getSut();
        $sThumbName = $this->getImageSizeAsString(
            md5(self::FIXTURE_FILE) . '_thumb_',
            $sut->getDefaultThumbnailSize()
        );

        return [
            [
                'sFile'      => '',
                'imageSize' => null,
                'expected'   => 'thumbs',
            ],
            [
                'sFile'      => self::FIXTURE_FILE,
                'imageSize' => null,
                'expected'   => 'thumbs/' . $sThumbName,
            ],
            [
                'sFile'      => '111.jpg',
                'imageSize' => null,
                'expected'   => false,
            ],
        ];
    }


    private function getImageSizeAsString(string $prefix, int $imageSize, $suffix = '.jpg'): string
    {
        return sprintf(
            '%s%d*%d%s',
            $prefix,
            $imageSize,
            $imageSize,
            $suffix
        );
    }

    protected function getSut(
        ?Config $shopConfig = null,
        ?ModuleSettings $moduleSettings = null,
        ?ThumbnailGeneratorInterface $thumbnailGenerator = null,
        ?ConnectionProviderInterface $connectionProvider = null
    ) {
        return new ImageResource(
            $shopConfig ?: $this->createStub(Config::class),
            $moduleSettings ?: $this->createStub(ModuleSettings::class),
            $thumbnailGenerator ?: $this->createStub(ThumbnailGeneratorInterface::class),
            $connectionProvider ?: $this->createStub(ConnectionProviderInterface::class)
        );
    }
}
