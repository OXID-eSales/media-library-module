<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Unit\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\MediaLibrary\Image\Service\ImageResource;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailGeneratorInterface;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Service\FileSystemService;
use OxidEsales\MediaLibrary\Service\FileSystemServiceInterface;
use OxidEsales\MediaLibrary\Service\FolderServiceInterface;
use OxidEsales\MediaLibrary\Service\Media;
use OxidEsales\MediaLibrary\Service\ModuleSettings;
use OxidEsales\MediaLibrary\Service\NamingService;
use OxidEsales\MediaLibrary\Service\NamingServiceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Service\Media
 */
class MediaTest extends TestCase
{
    private const FIXTURE_FILE = 'file.jpg';
    private const FIXTURE_FOLDER = 'some_folder';

    /**
     * @dataProvider getRenameDataProvider
     */
    public function testRename($structure, $oldName, $newName, $structureExpected, $folder, $expectedNewName, $type)
    {
        $directory = vfsStream::setup('root', 0777, $structure);

        $shopConfigMock = $this->createPartialMock(Config::class, ['getConfigParam']);
        $shopConfigMock->expects($this->any())
            ->method('getConfigParam')
            ->willReturnMap(
                [
                    ['sShopDir', null, $directory->url()],
                ]
            );

        $connectionMock = $this->createPartialMock(Connection::class, ['executeQuery']);
        $connectionMock->expects($this->once())->method('executeQuery');
        $connectionProviderStub = $this->createConfiguredMock(ConnectionProviderInterface::class, [
            'get' => $connectionMock,
        ]);

        $sut = $this->getSut(
            shopConfig: $shopConfigMock,
            connectionProvider: $connectionProviderStub,
            namingService: ContainerFactory::getInstance()->getContainer()->get(NamingServiceInterface::class),
        );
        if ($folder) {
            $sut->imageResource->setFolderName($folder);
        }
        $aSuccess = $sut->rename($oldName, $newName, '', $type);

        $this->assertEquals(['success' => true, 'filename' => $expectedNewName], $aSuccess);

        $this->assertEquals(
            $structureExpected,
            vfsStream::inspect(new vfsStreamStructureVisitor(), $directory)->getStructure()
        );
    }

    public function testMoveFile()
    {
        $sTargetFolderName = 'new_folder';
        $sTargetFolderID = '9999';
        $sSourceFileName = self::FIXTURE_FILE;
        $sSourceFileID = '111';
        $sThumbName = $this->getImageSizeAsString(
            '111_thumb_',
            $this->getSut()->imageResource->getDefaultThumbnailSize()
        );

        $structure['out']['pictures']['ddmedia'][$sSourceFileName] = 'some file';
        $structure['out']['pictures']['ddmedia']['thumbs'][$sThumbName] = 'some file';
        $structure['out']['pictures']['ddmedia'][$sTargetFolderName] = [];
        $directory = vfsStream::setup('root', 0777, $structure);

        $shopConfigMock = $this->createPartialMock(Config::class, ['getConfigParam']);
        $shopConfigMock->expects($this->any())
            ->method('getConfigParam')
            ->willReturnMap(
                [
                    ['sShopDir', null, $directory->url()],
                ]
            );

        $sSelect = "SELECT DDFILENAME FROM ddmedia WHERE OXID = ?";
        $connectionMock = $this->createPartialMock(
            Connection::class,
            ['fetchOne', 'fetchAllAssociative', 'executeQuery']
        );
        $connectionMock->expects($this->exactly(1))
            ->method('fetchOne')
            ->willReturn($sTargetFolderName);

        $connectionMock->expects($this->exactly(1))
            ->method('fetchAllAssociative')
            ->willReturn(
                [
                    0 => [
                        'DDFILENAME' => $sSourceFileName,
                        'DDTHUMB' => $sThumbName,
                    ],
                ]
            );

        $connectionMock->expects($this->once())
            ->method('executeQuery');

        $connectionProviderStub = $this->createConfiguredMock(
            ConnectionProviderInterface::class,
            [
                'get' => $connectionMock,
            ]
        );

        $sut = $this->getSut(
            shopConfig: $shopConfigMock,
            connectionProvider: $connectionProviderStub,
        );

        $sut->moveFileToFolder($sSourceFileID, $sTargetFolderID, $sThumbName);

        $structureExpected['root'] = $structure;
        unset($structureExpected['root']['out']['pictures']['ddmedia'][$sSourceFileName]);
        unset($structureExpected['root']['out']['pictures']['ddmedia']['thumbs'][$sThumbName]);
        $structureExpected['root']['out']['pictures']['ddmedia'][$sTargetFolderName] = [
            $sSourceFileName => 'some file',
            'thumbs' => [
                $sThumbName => 'some file',
            ],
        ];

        $this->assertEquals(
            $structureExpected,
            vfsStream::inspect(new vfsStreamStructureVisitor(), $directory)->getStructure()
        );
    }

    /**
     * @dataProvider getDeleteDataProvider
     * @return void
     */
    public function testDelete($structure, $structureExpected, $aIds, $aDBData, $startFolder)
    {
        $directory = vfsStream::setup('root', 0777, $structure);

        $shopConfigMock = $this->createPartialMock(Config::class, ['getConfigParam']);
        $shopConfigMock->expects($this->any())
            ->method('getConfigParam')
            ->willReturnMap(
                [
                    ['sShopDir', null, $directory->url()],
                ]
            );

        $connectionMock = $this->createPartialMock(
            Connection::class,
            [
                'fetchAllAssociative',
                'executeQuery',
                'quote',
            ]
        );
        $connectionMock->expects($this->once())
            ->method('fetchAllAssociative')
            ->willReturn($aDBData);

        $connectionMock->expects($this->any())
            ->method('executeQuery');

        $connectionMock->expects($this->any())
            ->method('quote');

        $connectionProviderStub = $this->createConfiguredMock(
            ConnectionProviderInterface::class,
            [
                'get' => $connectionMock,
            ]
        );

        $sut = $this->getSut(
            shopConfig: $shopConfigMock,
            connectionProvider: $connectionProviderStub,
        );
        if ($startFolder) {
            $sut->imageResource->setFolderName($startFolder);
        }
        $sut->delete($aIds);

        $this->assertEquals(
            $structureExpected,
            vfsStream::inspect(new vfsStreamStructureVisitor(), $directory)->getStructure()
        );
    }

    public function testUploadMedia()
    {
        $sThumbName = $this->getImageSizeAsString(
            md5(self::FIXTURE_FILE) . '_thumb_',
            $this->getSut()->imageResource->getDefaultThumbnailSize()
        );
        $sFile2 = 'file_1.jpg';
        $sThumbName1 = $this->getImageSizeAsString(
            md5($sFile2) . '_thumb_',
            $this->getSut()->imageResource->getDefaultThumbnailSize()
        );
        $structure['out']['pictures']['ddmedia'] = [
            self::FIXTURE_FILE => 'some file',
            'thumbs' => [$sThumbName => 'some file', $sThumbName1 => 'some file'],
        ];
        $structure['tmp'] = ['uploaded.jpg' => 'some file'];
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

        $connectionMock = $this->createPartialMock(
            Connection::class,
            [
                'executeQuery',
            ]
        );

        $connectionMock->expects($this->any())
            ->method('executeQuery');

        $connectionProviderStub = $this->createConfiguredMock(
            ConnectionProviderInterface::class,
            [
                'get' => $connectionMock,
            ]
        );

        $sId = md5(self::FIXTURE_FILE);

        $sut = $this->getSut(
            shopConfig: $shopConfigMock,
            connectionProvider: $connectionProviderStub,
            namingService: $this->createPartialMock(NamingService::class, [])
        );

        $sSourcePath = $directory->url() . '/tmp/uploaded.jpg';
        $sDestPath = $sut->imageResource->getMediaPath() . self::FIXTURE_FILE;
        $sFileSize = '1024';
        $sFileType = 'image/jpeg';
        $sut->uploadMedia($sSourcePath, $sDestPath, $sFileSize, $sFileType);

        $structureExpected['root'] = [
            'tmp' => [],
            'out' => [
                'pictures' => [
                    'ddmedia' => [
                        self::FIXTURE_FILE => 'some file',
                        $sFile2 => 'some file',
                        'thumbs' => [
                            $sThumbName => 'some file',
                            $sThumbName1 => 'some file',
                        ],
                    ],
                ],
            ],
        ];
        $this->assertEquals(
            $structureExpected,
            vfsStream::inspect(new vfsStreamStructureVisitor(), $directory)->getStructure()
        );

        $sDestPath = $sut->imageResource->getMediaPath() . 'test.js';
        $sFileType = 'text/javascript';
        $this->expectException(\Exception::class);
        $sut->uploadMedia($sSourcePath, $sDestPath, $sFileSize, $sFileType);
    }

    public function getRenameDataProvider(): array
    {
        $oMedia = $this->getSut(
            namingService: ContainerFactory::getInstance()->getContainer()->get(NamingServiceInterface::class),
        );

        $defaultThumbnailSize = $oMedia->imageResource->getDefaultThumbnailSize();

        $sThumbName = $this->getImageSizeAsString(
            md5(self::FIXTURE_FILE) . '_thumb_',
            $defaultThumbnailSize
        );
        $sThumbNameNew = $this->getImageSizeAsString(
            md5('new.jpg') . '_thumb_',
            $defaultThumbnailSize
        );

        $structure['out']['pictures']['ddmedia'][self::FIXTURE_FILE] = 'some file';
        $structure['out']['pictures']['ddmedia']['thumbs'][$sThumbName] = 'some file';
        $structureExpected['root']['out']['pictures']['ddmedia']['new.jpg'] = 'some file';
        $structureExpected['root']['out']['pictures']['ddmedia']['thumbs'][$sThumbNameNew] = 'some file';

        $sThumbName = $this->getImageSizeAsString(
            md5(self::FIXTURE_FILE) . '_thumb_',
            $defaultThumbnailSize
        );
        $structure1['out']['pictures']['ddmedia'][self::FIXTURE_FOLDER][self::FIXTURE_FILE] = 'some file';
        $structure1['out']['pictures']['ddmedia'][self::FIXTURE_FOLDER]['thumbs'][$sThumbName] = 'some file';
        $structureExpected1 = [
            'root' => [
                'out' => [
                    'pictures' => [
                        'ddmedia' => [
                            self::FIXTURE_FOLDER => [
                                'new.jpg' => 'some file',
                                'thumbs' => [
                                    $sThumbNameNew => 'some file',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $structure2['out']['pictures']['ddmedia'][self::FIXTURE_FOLDER] = [];
        $structureExpected2['root']['out']['pictures']['ddmedia']['folderNew'] = [];

        $sThumbName = $this->getImageSizeAsString(
            md5(self::FIXTURE_FILE) . '_thumb_',
            $defaultThumbnailSize
        );
        $sThumbName2 = $this->getImageSizeAsString(
            md5('new_1.jpg') . '_thumb_',
            $defaultThumbnailSize
        );
        $sThumbName3 = $this->getImageSizeAsString(
            md5('new_1.jpg') . '_thumb_',
            $defaultThumbnailSize
        );
        $sThumbNameNew2 = $this->getImageSizeAsString(
            md5('new_2.jpg') . '_thumb_',
            $defaultThumbnailSize
        );

        $structure3 = [
            'out' => [
                'pictures' => [
                    'ddmedia' => [
                        self::FIXTURE_FOLDER => [
                            self::FIXTURE_FILE => 'some file',
                            'new.jpg' => 'some file',
                            'new_1.jpg' => 'some file',
                            'thumbs' => [
                                $sThumbName => 'some file',
                                $sThumbName2 => 'some file',
                                $sThumbName3 => 'some file',
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $structureExpected3 = [
            'root' => [
                'out' => [
                    'pictures' => [
                        'ddmedia' => [
                            self::FIXTURE_FOLDER => [
                                'new.jpg' => 'some file',
                                'new_1.jpg' => 'some file',
                                'new_2.jpg' => 'some file',
                                'thumbs' => [
                                    $sThumbName2 => 'some file',
                                    $sThumbName3 => 'some file',
                                    $sThumbNameNew2 => 'some file',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return [
            [
                'structure' => $structure,
                'oldName' => self::FIXTURE_FILE,
                'newName' => 'new.jpg',
                'structureExpected' => $structureExpected,
                'folder' => '',
                'expectedNewName' => 'new.jpg',
                'type' => 'file',
            ],
            [
                'structure' => $structure1,
                'oldName' => self::FIXTURE_FILE,
                'newName' => 'new.jpg',
                'structureExpected' => $structureExpected1,
                'folder' => self::FIXTURE_FOLDER,
                'expectedNewName' => 'new.jpg',
                'type' => 'file',
            ],
            [
                'structure' => $structure2,
                'oldName' => self::FIXTURE_FOLDER,
                'newName' => 'folderNew',
                'structureExpected' => $structureExpected2,
                'folder' => '',
                'expectedNewName' => 'folderNew',
                'type' => 'folder',
            ],
            [
                'structure' => $structure3,
                'oldName' => self::FIXTURE_FILE,
                'newName' => 'new.jpg',
                'structureExpected' => $structureExpected3,
                'folder' => self::FIXTURE_FOLDER,
                'expectedNewName' => 'new_2.jpg',
                'type' => 'file',
            ],
        ];
    }

    public function getDeleteDataProvider()
    {
        $oMedia = $this->getSut();
        $sThumbName = $this->getImageSizeAsString(
            '111_thumb_',
            $oMedia->imageResource->getDefaultThumbnailSize()
        );

        // scenario 1 - file in media root
        $structure['out']['pictures']['ddmedia'][self::FIXTURE_FILE] = 'some file';
        $structure['out']['pictures']['ddmedia']['thumbs'][$sThumbName] = 'some file';
        $structureExpected['root']['out']['pictures']['ddmedia']['thumbs'] = [];
        $aIds = ['111'];
        $aDBData[] = [
            'OXID' => '111',
            'DDFILENAME' => self::FIXTURE_FILE,
            'DDTHUMB' => $sThumbName,
            'DDFILETYPE' => 'image/jpeg',
            'DDFOLDERID' => '',
        ];

        // scenario 2 - file in a folder
        $structure1['out']['pictures']['ddmedia'][self::FIXTURE_FOLDER][self::FIXTURE_FILE] = 'some file';
        $structure1['out']['pictures']['ddmedia'][self::FIXTURE_FOLDER]['thumbs'][$sThumbName] = 'some file';
        $structureExpected1['root']['out']['pictures']['ddmedia'][self::FIXTURE_FOLDER]['thumbs'] = [];
        $aIds1 = ['111'];
        $aDBData1[] = [
            'OXID' => '111',
            'DDFILENAME' => self::FIXTURE_FILE,
            'DDTHUMB' => $sThumbName,
            'DDFILETYPE' => 'image/jpeg',
            'DDFOLDERID' => '2222',
        ];

        // scenario 3 - empty folder
        $structure2['out']['pictures']['ddmedia'][self::FIXTURE_FOLDER] = [];
        $structure2['out']['pictures']['ddmedia'][self::FIXTURE_FOLDER]['thumbs'] = [];
        $structureExpected2['root']['out']['pictures']['ddmedia'] = [];
        $aIds2 = ['111'];
        $aDBData2[] = [
            'OXID' => '111',
            'DDFILENAME' => self::FIXTURE_FOLDER,
            'DDTHUMB' => '',
            'DDFILETYPE' => 'directory',
            'DDFOLDERID' => '',
        ];

        // scenario 4 - folder with files
        $structure3['out']['pictures']['ddmedia'][self::FIXTURE_FOLDER][self::FIXTURE_FILE] = 'some file';
        $structure3['out']['pictures']['ddmedia'][self::FIXTURE_FOLDER]['thumbs'][$sThumbName] = 'some file';
        $structureExpected3['root']['out']['pictures']['ddmedia'] = [];
        $aIds3 = ['111'];
        $aDBData3[] = [
            'OXID' => '111',
            'DDFILENAME' => self::FIXTURE_FOLDER,
            'DDTHUMB' => '',
            'DDFILETYPE' => 'directory',
            'DDFOLDERID' => '',
        ];
        $aDBData3[] = [
            'OXID' => '222',
            'DDFILENAME' => self::FIXTURE_FILE,
            'DDTHUMB' => $sThumbName,
            'DDFILETYPE' => 'image/jpeg',
            'DDFOLDERID' => '111',
        ];

        return [
            [
                'structure' => $structure,
                'structureExpected' => $structureExpected,
                'aIds' => $aIds,
                'aDBData' => $aDBData,
                'startFolder' => '',
            ],
            [
                'structure' => $structure1,
                'structureExpected' => $structureExpected1,
                'aIds' => $aIds1,
                'aDBData' => $aDBData1,
                'startFolder' => self::FIXTURE_FOLDER,
            ],
            [
                'structure' => $structure2,
                'structureExpected' => $structureExpected2,
                'aIds' => $aIds2,
                'aDBData' => $aDBData2,
                'startFolder' => '',
            ],
            [
                'structure' => $structure3,
                'structureExpected' => $structureExpected3,
                'aIds' => $aIds3,
                'aDBData' => $aDBData3,
                'startFolder' => '',
            ],
        ];
    }

    protected function getSut(
        ?ModuleSettings $moduleSettings = null,
        ?Config $shopConfig = null,
        ?ConnectionProviderInterface $connectionProvider = null,
        ?UtilsObject $utilsObject = null,
        ?ThumbnailGeneratorInterface $thumbnailGenerator = null,
        ?NamingServiceInterface $namingService = null,
        ?MediaRepositoryInterface $mediaRepository = null,
        ?FileSystemServiceInterface $fileSystemService = null,
    ) {
        $imageResourceMock = $this->getImageResourceStub(
            $shopConfig,
            $moduleSettings,
            $thumbnailGenerator,
            $connectionProvider
        );
        return new MediaMock(
            shopConfig: $shopConfig ?: $this->createStub(Config::class),
            connectionProvider: $connectionProvider ?: $this->createStub(ConnectionProviderInterface::class),
            imageResource: $imageResourceMock,
            namingService: $namingService ?? $this->createStub(NamingServiceInterface::class),
            mediaRepository: $mediaRepository ?? $this->createStub(MediaRepositoryInterface::class),
            fileSystemService: $fileSystemService ?? $this->createPartialMock(FileSystemService::class, []),
            shopAdapter: $this->createStub(ShopAdapterInterface::class),
        );
    }

    protected function getImageResourceStub(
        ?Config $shopConfig = null,
        ?ModuleSettings $moduleSettings = null,
        ?ThumbnailGeneratorInterface $thumbnailGenerator = null,
        ?ConnectionProviderInterface $connectionProvider = null,
    ) {
        return new ImageResource(
            $shopConfig ?: $this->createStub(Config::class),
            $moduleSettings ?: $this->createStub(ModuleSettings::class),
            $thumbnailGenerator ?: $this->createStub(ThumbnailGeneratorInterface::class),
            $connectionProvider ?: $this->createStub(ConnectionProviderInterface::class),
        );
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
}
