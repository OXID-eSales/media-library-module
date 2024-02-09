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
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\MediaLibrary\Image\DataTransfer\FilePath;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\MediaLibrary\Image\Service\ImageResource;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactoredInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailGeneratorInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailResourceInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailServiceInterface;
use OxidEsales\MediaLibrary\Media\DataType\Media;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
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

    protected function getSut(
        ?ModuleSettings $moduleSettings = null,
        ?Config $shopConfig = null,
        ?ConnectionProviderInterface $connectionProvider = null,
        ?ThumbnailGeneratorInterface $thumbnailGenerator = null,
        ?NamingServiceInterface $namingService = null,
        ?MediaRepositoryInterface $mediaRepository = null,
        ?FileSystemServiceInterface $fileSystemService = null,
        ?ImageResourceRefactoredInterface $imageResourceRef = null,
        ?ThumbnailResourceInterface $thumbnailResource = null,
        ?ThumbnailServiceInterface $thumbnailService = null,
    ) {
        $imageResourceMock = $this->getImageResourceStub(
            $shopConfig,
            $moduleSettings,
            $thumbnailGenerator,
            $connectionProvider
        );
        return new MediaMock(
            imageResource: $imageResourceMock,
            namingService: $namingService ?? $this->createStub(NamingServiceInterface::class),
            mediaRepository: $mediaRepository ?? $this->createStub(MediaRepositoryInterface::class),
            fileSystemService: $fileSystemService ?? $this->createPartialMock(FileSystemService::class, []),
            shopAdapter: $this->createStub(ShopAdapterInterface::class),
            UIRequest: $this->createStub(UIRequestInterface::class),
            imageResourceRefactored: $imageResourceRef ?? $this->createStub(ImageResourceRefactoredInterface::class),
            thumbnailService: $thumbnailService ?? $this->createStub(ThumbnailServiceInterface::class)
        );
    }

    protected function getImageResourceStub(
        ?Config $shopConfig = null,
        ?ModuleSettings $moduleSettings = null,
        ?ThumbnailGeneratorInterface $thumbnailGenerator = null,
        ?ConnectionProviderInterface $connectionProvider = null,
    ) {
        return new ImageResource(
            shopConfig: $shopConfig ?: $this->createStub(Config::class),
            moduleSettings: $moduleSettings ?: $this->createStub(ModuleSettings::class),
            thumbnailGenerator: $thumbnailGenerator ?: $this->createStub(ThumbnailGeneratorInterface::class),
            connectionProvider: $connectionProvider ?: $this->createStub(ConnectionProviderInterface::class),
            fileSystemService: $this->createStub(FileSystemServiceInterface::class)
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
            thumbnailService: $thumbnailServiceSpy = $this->createMock(ThumbnailServiceInterface::class),
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

        $thumbnailServiceSpy->expects($this->once())->method('deleteMediaThumbnails')->with($exampleMedia);

        $repositorySpy->expects($this->once())->method('deleteMedia')->with($mediaId);
        $fileSystemSpy->expects($this->once())->method('delete')->with($mediaFilePath);

        $sut->deleteMedia($exampleMedia);
    }

    public function testRename(): void
    {
        $sut = $this->getSut(
            namingService: $namingMock = $this->createMock(NamingServiceInterface::class),
            mediaRepository: $repositorySpy = $this->createMock(MediaRepositoryInterface::class),
            fileSystemService: $fileSystemSpy = $this->createMock(FileSystemServiceInterface::class),
            imageResourceRef: $imageResource = $this->createStub(ImageResourceRefactoredInterface::class),
            thumbnailService: $thumbnailServiceSpy = $this->createMock(ThumbnailServiceInterface::class),
        );

        $mediaId = uniqid();
        $mediaFolderName = uniqid();
        $mediaFileName = uniqid();

        $mediaStub = $this->createStub(MediaInterface::class);
        $mediaStub->method('getFolderName')->willReturn($mediaFolderName);
        $mediaStub->method('getFileName')->willReturn($mediaFileName);
        $repositorySpy->method('getMediaById')->with($mediaId)->willReturn($mediaStub);

        $thumbnailServiceSpy->expects($this->once())->method('deleteMediaThumbnails')->with($mediaStub);

        $oldPath = 'exampleOldFilePath';
        $mediaFolderPath = 'mediaFolderPath';
        $imageResource->method('getPathToMediaFile')->with($mediaStub)->willReturn($oldPath);
        $imageResource->method('getPathToMediaFiles')->with($mediaFolderName)->willReturn($mediaFolderPath);

        $newMediaNameInput = 'someFileName';
        $newSanitizedMediaName = 'someSanitizedFileName.txt';
        $namingMock->method('sanitizeFilename')->with($newMediaNameInput)->willReturn($newSanitizedMediaName);

        $newSanitizedUniquePath = $mediaFolderPath . '/someSanitizedUniqueFileName.txt';
        $imageResource->method('getPossibleMediaFilePath')
            ->with($mediaFolderName, $newSanitizedMediaName)
            ->willReturn(new FilePath($newSanitizedUniquePath));

        $renameResultStub = $this->createStub(MediaInterface::class);
        $repositorySpy->expects($this->once())->method('renameMedia')
            ->with($mediaId, 'someSanitizedUniqueFileName.txt')
            ->willReturn($renameResultStub);

        $fileSystemSpy->expects($this->once())->method('rename')->with(
            $oldPath,
            $mediaFolderPath . '/someSanitizedUniqueFileName.txt'
        );

        $this->assertSame($renameResultStub, $sut->rename($mediaId, $newMediaNameInput));
    }

    public function testMoveToFolder(): void
    {
        $sut = $this->getSut(
            mediaRepository: $repositorySpy = $this->createMock(MediaRepositoryInterface::class),
            fileSystemService: $fileSystemSpy = $this->createMock(FileSystemServiceInterface::class),
            imageResourceRef: $imageResource = $this->createStub(ImageResourceRefactoredInterface::class),
            thumbnailService: $thumbnailServiceSpy = $this->createMock(ThumbnailServiceInterface::class),
        );

        $mediaId = uniqid();
        $newFolderId = uniqid();

        $newFolderName = 'someFolderName';
        $folderStub = $this->createStub(MediaInterface::class);
        $folderStub->method('getFolderName')->willReturn('');
        $folderStub->method('getFileName')->willReturn($newFolderName);

        $mediaFolderName = uniqid();
        $mediaFileName = uniqid();
        $mediaStub = $this->createStub(MediaInterface::class);
        $mediaStub->method('getFolderName')->willReturn($mediaFolderName);
        $mediaStub->method('getFileName')->willReturn($mediaFileName);

        $repositorySpy->method('getMediaById')->willReturnMap([
            [$newFolderId, $folderStub],
            [$mediaId, $mediaStub]
        ]);

        $repositorySpy->expects($this->once())->method('changeMediaFolderId')->with($mediaId, $newFolderId);

        $thumbnailServiceSpy->expects($this->once())->method('deleteMediaThumbnails')->with($mediaStub);

        $oldPath = 'exampleOldFilePath';
        $mediaFolderPath = 'mediaFolderPath';

        $imageResource->method('getPathToMediaFile')->with($mediaStub)->willReturn($oldPath);
        $imageResource->method('getPathToMediaFiles')->with($newFolderName)->willReturn($mediaFolderPath);

        $newUniquePath = $mediaFolderPath . '/someUniqueFileName.txt';
        $imageResource->method('getPossibleMediaFilePath')
            ->with($newFolderName, $mediaFileName)
            ->willReturn(new FilePath($newUniquePath));

        $repositorySpy->expects($this->once())->method('renameMedia')->with($mediaId, 'someUniqueFileName.txt');

        $fileSystemSpy->expects($this->once())->method('rename')->with(
            $oldPath,
            $newUniquePath
        );

        $sut->moveToFolder($mediaId, $newFolderId);
    }

    public function testUploadNew(): void
    {
        $sut = $this->getSut(
            namingService: $namingMock = $this->createMock(NamingServiceInterface::class),
            mediaRepository: $repositorySpy = $this->createMock(MediaRepositoryInterface::class),
            fileSystemService: $fileSystemSpy = $this->createMock(FileSystemServiceInterface::class),
            imageResourceRef: $imageResource = $this->createStub(ImageResourceRefactoredInterface::class),
        );

        $newMediaId = uniqid();
        $newMediaName = 'someNewMediaName';
        $namingMock->method('getUniqueId')->willReturn($newMediaId);

        $folderId = 'someFolderId';
        $folderName = 'someNewFolderName';
        $folderMediaStub = $this->createStub(MediaInterface::class);
        $folderMediaStub->method('getFileName')->willReturn($folderName);

        $newMediaStub = $this->createStub(MediaInterface::class);

        $repositorySpy->method('getMediaById')->willReturnMap([
            [$folderId, $folderMediaStub],
            [$newMediaId, $newMediaStub]
        ]);

        $newUniquePath = 'mediapath/' . $folderName . '/' . $newMediaName;
        $imageResource->method('getPossibleMediaFilePath')
            ->with($folderName, $newMediaName)
            ->willReturn(new FilePath($newUniquePath));

        $uploadedFilePath = 'someUploadedFilePath';
        $fileSystemSpy->expects($this->once())->method('moveUploadedFile')->with(
            $uploadedFilePath,
            $newUniquePath
        );

        $imageSizeStub = $this->createStub(ImageSizeInterface::class);
        $fileSystemSpy->method('getImageSize')->with($newUniquePath)->willReturn($imageSizeStub);
        $fileSystemSpy->method('getFileSize')->with($newUniquePath)->willReturn(12345);
        $fileSystemSpy->method('getMimeType')->with($newUniquePath)->willReturn('someMimeType');

        $repositorySpy->expects($this->once())->method('addMedia')->with(
            $this->callback(function (MediaInterface $media) use (
                $folderId,
                $newMediaId,
                $newMediaName,
                $imageSizeStub
            ) {
                $this->assertSame($newMediaId, $media->getOxid());
                $this->assertSame($newMediaName, $media->getFileName());
                $this->assertSame($folderId, $media->getFolderId());
                $this->assertSame($imageSizeStub, $media->getImageSize());
                $this->assertSame('someMimeType', $media->getFileType());
                $this->assertSame(12345, $media->getFileSize());
                return true;
            })
        );

        $this->assertSame($newMediaStub, $sut->upload($uploadedFilePath, $folderId, $newMediaName));
    }
}
