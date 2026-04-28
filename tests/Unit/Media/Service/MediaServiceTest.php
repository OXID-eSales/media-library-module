<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailServiceInterface;
use OxidEsales\MediaLibrary\Media\DataType\FilePath;
use OxidEsales\MediaLibrary\Media\DataType\Media;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaObjectResourceInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaResourceInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaService;
use OxidEsales\MediaLibrary\Service\FileSystemService;
use OxidEsales\MediaLibrary\Service\FileSystemServiceInterface;
use OxidEsales\MediaLibrary\Service\NamingServiceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MediaService::class)]
class MediaServiceTest extends TestCase
{
    protected function getSut(
        ?NamingServiceInterface $namingService = null,
        ?MediaRepositoryInterface $mediaRepository = null,
        ?FileSystemServiceInterface $fileSystemService = null,
        ?MediaResourceInterface $mediaResource = null,
        ?MediaObjectResourceInterface $mediaObjectResource = null,
        ?ThumbnailServiceInterface $thumbnailService = null,
    ) {
        return new MediaService(
            namingService: $namingService ?? $this->createStub(NamingServiceInterface::class),
            mediaRepository: $mediaRepository ?? $this->createStub(MediaRepositoryInterface::class),
            fileSystemService: $fileSystemService ?? $this->createPartialMock(FileSystemService::class, []),
            mediaResource: $mediaResource ?? $this->createStub(MediaResourceInterface::class),
            thumbnailService: $thumbnailService ?? $this->createStub(ThumbnailServiceInterface::class),
            mediaObjectResource: $mediaObjectResource ?? $this->createStub(MediaObjectResourceInterface::class),
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
            thumbnailService: $thumbnailServiceSpy = $this->createMock(ThumbnailServiceInterface::class),
            mediaObjectResource: $mediaObjectResource = $this->createMock(MediaObjectResourceInterface::class),
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
        $mediaObjectResource->method('getPathToMedia')
            ->with($exampleMedia)
            ->willReturn($mediaFilePath);

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
            mediaResource: $mediaResource = $this->createStub(MediaResourceInterface::class),
            thumbnailService: $thumbnailServiceSpy = $this->createMock(ThumbnailServiceInterface::class),
            mediaObjectResource: $mediaObjectResource = $this->createMock(MediaObjectResourceInterface::class),
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
        $mediaObjectResource->method('getPathToMedia')->with($mediaStub)->willReturn($oldPath);
        $mediaResource->method('getPathToMediaFiles')->with($mediaFolderName)->willReturn($mediaFolderPath);

        $newMediaNameInput = 'someFileName';
        $newSanitizedMediaName = 'someSanitizedFileName.txt';
        $namingMock->method('sanitizeFilename')->with($newMediaNameInput)->willReturn($newSanitizedMediaName);

        $newSanitizedUniquePath = $mediaFolderPath . '/someSanitizedUniqueFileName.txt';
        $mediaResource->method('getPossibleMediaFilePath')
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
            mediaResource: $mediaResource = $this->createStub(MediaResourceInterface::class),
            thumbnailService: $thumbnailServiceSpy = $this->createMock(ThumbnailServiceInterface::class),
            mediaObjectResource: $mediaObjectResource = $this->createMock(MediaObjectResourceInterface::class),
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

        $mediaObjectResource->method('getPathToMedia')->with($mediaStub)->willReturn($oldPath);
        $mediaResource->method('getPathToMediaFiles')->with($newFolderName)->willReturn($mediaFolderPath);

        $newUniquePath = $mediaFolderPath . '/someUniqueFileName.txt';
        $mediaResource->method('getPossibleMediaFilePath')
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
            mediaResource: $imageResource = $this->createStub(MediaResourceInterface::class),
        );

        $newMediaId = uniqid();
        $newMediaName = 'someNewMediaName';
        $namingMock->method('getUniqueId')->willReturn($newMediaId);
        $namingMock->method('sanitizeFilename')->willReturnArgument(0);

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

    #[Test]
    public function uploadSanitizesFilenameToPreventPathTraversal(): void
    {
        $sut = $this->getSut(
            namingService: $namingMock = $this->createMock(NamingServiceInterface::class),
            mediaRepository: $repositoryStub = $this->createMock(MediaRepositoryInterface::class),
            fileSystemService: $fileSystemSpy = $this->createMock(FileSystemServiceInterface::class),
            mediaResource: $mediaResourceMock = $this->createMock(MediaResourceInterface::class),
        );

        $newMediaId = uniqid();
        $namingMock->method('getUniqueId')->willReturn($newMediaId);

        $folderId = uniqid();
        $folderName = uniqid();
        $folderMediaStub = $this->createStub(MediaInterface::class);
        $folderMediaStub->method('getFileName')->willReturn($folderName);

        $newMediaStub = $this->createStub(MediaInterface::class);
        $repositoryStub->method('getMediaById')->willReturnMap([
            [$folderId, $folderMediaStub],
            [$newMediaId, $newMediaStub],
        ]);

        $maliciousFileName = 'a/../../../source/modules/tainted.svg';
        $sanitizedFileName = 'a-.-.-.-source-modules-tainted.svg';

        $namingMock->expects($this->once())
            ->method('sanitizeFilename')
            ->with($maliciousFileName)
            ->willReturn($sanitizedFileName);

        $safePath = 'mediapath/' . $folderName . '/' . $sanitizedFileName;
        $mediaResourceMock->expects($this->once())
            ->method('getPossibleMediaFilePath')
            ->with($folderName, $sanitizedFileName)
            ->willReturn(new FilePath($safePath));

        $uploadedFilePath = uniqid();
        $fileSystemSpy->expects($this->once())
            ->method('moveUploadedFile')
            ->with($uploadedFilePath, $safePath);

        $fileSystemSpy->method('getImageSize')
            ->willReturn($this->createStub(ImageSizeInterface::class));
        $fileSystemSpy->method('getFileSize')->willReturn(0);
        $fileSystemSpy->method('getMimeType')->willReturn('image/svg+xml');

        $sut->upload($uploadedFilePath, $folderId, $maliciousFileName);
    }

    public function testGetMediaById(): void
    {
        $sut = $this->getSut(
            mediaRepository: $mediaRepositoryStub = $this->createStub(MediaRepositoryInterface::class)
        );

        $someId = uniqid();
        $mediaStub = $this->createStub(MediaInterface::class);
        $mediaRepositoryStub->method('getMediaById')->with($someId)->willReturn($mediaStub);

        $this->assertSame($mediaStub, $sut->getMediaById($someId));
    }
}
