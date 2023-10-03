<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Integration\Service;

use MyProject\Container;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProvider;
use OxidEsales\MediaLibrary\Image\Service\ImageResource;
use OxidEsales\MediaLibrary\Service\ModuleSettings;
use OxidEsales\MediaLibrary\Tests\Integration\IntegrationTestCase;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailGeneratorInterface;

class MediaTest extends IntegrationTestCase
{
    private const FIXTURE_FILE = 'file.jpg';
    private const FIXTURE_FOLDER = 'Folder';
    private \Psr\Container\ContainerInterface $containerFactory;
    /**
     * @return mixed
     */
    protected static function getConnection()
    {
        return ContainerFactory::getInstance()
            ->getContainer()->get(QueryBuilderFactoryInterface::class)
            ->create()
            ->getConnection();
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $connection = self::getConnection();
        $connection->executeStatement(
            'TRUNCATE ddmedia;'
        );
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->containerFactory = ContainerFactory::getInstance()->getContainer();
        $sFixturesImgPath = dirname(__FILE__) . '/../../fixtures/img/';
        $sTargetPath = Registry::getConfig()->getConfigParam('sShopDir') . '/tmp/';

        copy($sFixturesImgPath . 'image.jpg', $sTargetPath . 'image.jpg');
        copy($sFixturesImgPath . 'image.png', $sTargetPath . 'image.png');
        copy($sFixturesImgPath . 'image.gif', $sTargetPath . 'image.gif');
        copy($sFixturesImgPath . 'favicon.ico', $sTargetPath . 'favicon.ico');
    }

    /**
     * @dataProvider getUploadMediaDataProvider
     */
    public function testUploadMedia($imageName, $destFileName, $fileType)
    {
        $sut = $this->getSut();

        $sut->imageResource->setFolder();

        $sSourcePath = Registry::getConfig()->getConfigParam('sShopDir') . 'tmp/' . $imageName;
        $sDestPath = $sut->imageResource->getMediaPath() . $destFileName;
        $sFileSize = filesize($sSourcePath);
        $sFileType = $fileType;

        $aResult = $sut->uploadMedia($sSourcePath, $sDestPath, $sFileSize, $sFileType, true);

        $this->assertTrue(file_exists($sDestPath));
        $this->assertNotEmpty($aResult['id']);
        $this->assertNotFalse($aResult['thumb']);
    }

    /**
     * @dataProvider getUploadMediaDataProvider
     * @param $imageName
     * @param $destFileName
     * @param $fileType
     *
     * @return void
     */
    public function testUploadMediaInFolder($imageName, $destFileName, $fileType)
    {
        $sut = $this->getSut();

        $aResult = $sut->createCustomDir(self::FIXTURE_FOLDER);
        $sFolderId = $aResult['id'];
        $sFolderName = $aResult['dir'];

        $this->assertNotEmpty($sFolderId);

        $sut->imageResource->setFolder($sFolderId);

        $sSourcePath = Registry::getConfig()->getConfigParam('sShopDir') . 'tmp/' . $imageName;
        $sDestPath = $sut->imageResource->getMediaPath() . $destFileName;
        $sFileSize = filesize($sSourcePath);
        $sFileType = $fileType;
        $blCreateThumbs = true;

        $this->assertStringContainsString($sFolderName, $sDestPath);

        $aResult = $sut->uploadMedia($sSourcePath, $sDestPath, $sFileSize, $sFileType, $blCreateThumbs);

        $this->assertTrue(file_exists($sDestPath));
        $this->assertNotEmpty($aResult['id']);
        $this->assertNotFalse($aResult['thumb']);
        $this->assertStringContainsString($sFolderName, $aResult['thumb']);
        $sThumbFile = str_replace(
            Registry::getConfig()->getConfigParam('sShopURL'),
            Registry::getConfig()->getConfigParam('sShopDir'),
            $aResult['thumb']
        );
        $this->assertTrue(file_exists($sThumbFile));
    }

    /**
     * This test depends on the test testUploadMedia
     *
     * @return void
     */
    public function testFilesCount()
    {
        $sut = $this->getSut();
        $sut->imageResource->setFolder();
        $this->assertEquals(9, $sut->getFileCount()); // 4 uploads and 1 folder
    }

    /**
     * This test depends on the test testUploadMedia
     *
     * @return void
     */
    public function testGetFiles()
    {
        $sut = $this->getSut();
        $sut->imageResource->setFolder();
        $aFiles = $sut->getFiles();

        $this->assertGreaterThan(0, $this->count($aFiles));

        foreach ($aFiles as $aRow) {
            $aFilesResult[] = $aRow['DDFILENAME'];
        }

        $this->assertContains('file.jpg', $aFilesResult);
    }


    public function testCreateThumbnailException()
    {
        $sut = $this->getSut();

        $sut->imageResource->setFolder('');

        $sSourcePath = Registry::getConfig()->getConfigParam('sShopDir') . 'tmp/favicon.ico';
        $sDestPath = $sut->imageResource->getMediaPath() . 'favicon.ico';
        copy($sSourcePath, $sDestPath);

        $this->expectException(\Exception::class);
        $sut->imageResource->createThumbnail('favicon.ico');
    }

    public function testFolder()
    {
        $sut = $this->getSut();

        $sut->imageResource->setFolder('f256df3c2343b7e24ef5273c15f11e1b');
        $this->assertEquals('Folder1', $sut->imageResource->getFolderName());
    }

    public function getUploadMediaDataProvider()
    {
        return [
            [
                'imageName'    => 'image.jpg',
                'destFileName' => self::FIXTURE_FILE,
                'fileType'     => 'image/jpeg',
            ],
            [
                'imageName'    => 'image.png',
                'destFileName' => 'image.png',
                'fileType'     => 'image/png',
            ],
            [
                'imageName'    => 'image.gif',
                'destFileName' => 'image.gif',
                'fileType'     => 'image/gif',
            ],
            [
                'imageName'    => 'image.gif',
                'destFileName' => 'image.gif',
                'fileType'     => 'image/gif',
            ],
        ];
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $sMediaPath = Registry::getConfig()->getConfigParam('sShopDir') . 'out/pictures/ddmedia/';
        $sMediaThumbPath = $sMediaPath . '/thumbs/';

        foreach (glob($sMediaPath . '*') as $file) {
            if (is_dir($file)) {
                foreach (glob($file . '/*') as $file2) {
                    unlink($file2);
                }
                foreach (glob($file . '/thumbs/*') as $file2) {
                    unlink($file2);
                }
                rmdir($file . '/thumbs');
                rmdir($file);
            }
            unlink($file);
        }
        foreach (glob($sMediaThumbPath . '*') as $file) {
            unlink($file);
        }

        $connection = self::getConnection();
        $connection->executeStatement(
            'TRUNCATE ddmedia;'
        );
    }

    protected function getSut(
        ?ModuleSettings $moduleSettings = null,
        ?Config $shopConfig = null,
        ?ConnectionProviderInterface $connectionProvider = null,
        ?UtilsObject $utilsObject = null,
        ?ThumbnailGeneratorInterface $thumbnailGenerator = null
    ) {
        $imageResourceMock = $this->getImageResource(
            $shopConfig,
            $moduleSettings,
            $thumbnailGenerator,
            $connectionProvider,
        );
        return new MediaMock(
            $moduleSettings ?: $this->containerFactory->get(ModuleSettings::class),
            $shopConfig ?: Registry::getConfig(),
            $connectionProvider ?: new ConnectionProvider(),
            $utilsObject ?: Registry::getUtilsObject(),
            $thumbnailGenerator ?: $this->containerFactory->get(ThumbnailGeneratorInterface::class),
            $imageResourceMock,
        );
    }

    protected function getImageResource(
        ?Config $shopConfig = null,
        ?ModuleSettings $moduleSettings = null,
        ?ThumbnailGeneratorInterface $thumbnailGenerator = null,
        ?ConnectionProviderInterface $connectionProvider = null,
    ) {
        return new ImageResource(
            $shopConfig ?: Registry::getConfig(),
            $moduleSettings ?: $this->containerFactory->get(ModuleSettings::class),
            $thumbnailGenerator ?: $this->containerFactory->get(ThumbnailGeneratorInterface::class),
            $connectionProvider ?: new ConnectionProvider(),
        );
    }
}
