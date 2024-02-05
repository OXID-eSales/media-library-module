<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Unit\Service;

use Doctrine\DBAL\Connection;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\MediaLibrary\Image\Service\ImageResource;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactoredInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailGeneratorInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailResourceInterface;
use OxidEsales\MediaLibrary\Media\DataType\Media;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Service\FileSystemService;
use OxidEsales\MediaLibrary\Service\FileSystemServiceInterface;
use OxidEsales\MediaLibrary\Service\ModuleSettings;
use OxidEsales\MediaLibrary\Service\NamingService;
use OxidEsales\MediaLibrary\Service\NamingServiceInterface;
use OxidEsales\MediaLibrary\Transput\RequestData\UIRequestInterface;
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

        $mediaRepositorySpy = $this->createMock(MediaRepositoryInterface::class);
        $mediaRepositorySpy->expects($this->once())->method('renameMedia');

        $sut = $this->getSut(
            shopConfig: $shopConfigMock,
            mediaRepository: $mediaRepositorySpy,
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

    public static function getRenameDataProvider(): array
    {
        $defaultThumbnailSize = 185;

        $sThumbName = self::getImageSizeAsString(
            md5(self::FIXTURE_FILE) . '_thumb_',
            $defaultThumbnailSize
        );
        $sThumbNameNew = self::getImageSizeAsString(
            md5('new.jpg') . '_thumb_',
            $defaultThumbnailSize
        );

        $structure['out']['pictures']['ddmedia'][self::FIXTURE_FILE] = 'some file';
        $structure['out']['pictures']['ddmedia']['thumbs'][$sThumbName] = 'some file';
        $structureExpected['root']['out']['pictures']['ddmedia']['new.jpg'] = 'some file';
        $structureExpected['root']['out']['pictures']['ddmedia']['thumbs'][$sThumbNameNew] = 'some file';

        $sThumbName = self::getImageSizeAsString(
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

        $sThumbName = self::getImageSizeAsString(
            md5(self::FIXTURE_FILE) . '_thumb_',
            $defaultThumbnailSize
        );
        $sThumbName2 = self::getImageSizeAsString(
            md5('new_1.jpg') . '_thumb_',
            $defaultThumbnailSize
        );
        $sThumbName3 = self::getImageSizeAsString(
            md5('new_1.jpg') . '_thumb_',
            $defaultThumbnailSize
        );
        $sThumbNameNew2 = self::getImageSizeAsString(
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

    protected function getSut(
        ?ModuleSettings $moduleSettings = null,
        ?Config $shopConfig = null,
        ?ConnectionProviderInterface $connectionProvider = null,
        ?UtilsObject $utilsObject = null,
        ?ThumbnailGeneratorInterface $thumbnailGenerator = null,
        ?NamingServiceInterface $namingService = null,
        ?MediaRepositoryInterface $mediaRepository = null,
        ?FileSystemServiceInterface $fileSystemService = null,
        ?ImageResourceRefactoredInterface $imageResourceRef = null,
        ?ThumbnailResourceInterface $thumbnailResource = null,
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
            UIRequest: $this->createStub(UIRequestInterface::class),
            imageResourceRefactored: $imageResourceRef ?? $this->createStub(ImageResourceRefactoredInterface::class),
            thumbnailResource: $thumbnailResource ?? $this->createStub(ThumbnailResourceInterface::class)
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

    private static function getImageSizeAsString(string $prefix, int $imageSize, $suffix = '.jpg'): string
    {
        return sprintf(
            '%s%d*%d%s',
            $prefix,
            $imageSize,
            $imageSize,
            $suffix
        );
    }

    public function testDeleteRegularMedia(): void
    {
        $sut = $this->getSut(
            mediaRepository: $repositorySpy = $this->createMock(MediaRepositoryInterface::class),
            fileSystemService: $fileSystemSpy = $this->createMock(FileSystemServiceInterface::class),
            imageResourceRef: $imageResource = $this->createStub(ImageResourceRefactoredInterface::class),
            thumbnailResource: $thumbnailResource = $this->createStub(ThumbnailResourceInterface::class)
        );

        $mediaId = uniqid();
        $mediaFileName = 'someFileName';
        $folderName = 'someFolderName';

        $exampleMedia = new Media(
            oxid: $mediaId,
            fileName: $mediaFileName,
            fileType: uniqid(),
            folderName: $folderName
        );

        $mediaFilePath = 'exampleMediaFilePath';
        $imageResource->method('getPathToMediaFile')->with($exampleMedia)->willReturn($mediaFilePath);

        $thumbGlob = 'exampleThumbGlob';
        $thumbPath = 'exampleThumbPath';
        $thumbnailResource->method('getThumbnailsGlob')->with($mediaFileName)->willReturn($thumbGlob);
        $thumbnailResource->method('getPathToThumbnailFiles')->with($folderName)->willReturn($thumbPath);

        $repositorySpy->expects($this->once())->method('deleteMedia')->with($mediaId);
        $fileSystemSpy->expects($this->once())->method('delete')->with($mediaFilePath);
        $fileSystemSpy->expects($this->once())->method('deleteByGlob')->with($thumbPath, $thumbGlob);

        $sut->deleteMedia($exampleMedia);
    }
}
