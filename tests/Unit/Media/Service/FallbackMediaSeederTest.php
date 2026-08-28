<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Media\DataType\FilePath;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Media\Service\FallbackMediaResourceInterface;
use OxidEsales\MediaLibrary\Media\Service\FallbackMediaSeeder;
use OxidEsales\MediaLibrary\Media\Service\MediaResourceInterface;
use OxidEsales\MediaLibrary\Media\Settings\FallbackMediaSettingsInterface;
use OxidEsales\MediaLibrary\Service\FileSystemServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class FallbackMediaSeederTest extends TestCase
{
    #[Test]
    public function seedMediaCopiesTheShippedAssetIntoTheMediaDirectory(): void
    {
        $fileName = uniqid() . '.jpg';
        $sourcePath = '/module/assets/' . $fileName;
        $targetPath = '/shop/out/pictures/ddmedia/' . $fileName;

        $fileSystemSpy = $this->createMock(FileSystemServiceInterface::class);
        $fileSystemSpy->expects($this->once())
            ->method('copy')
            ->with($sourcePath, $targetPath);

        $sut = $this->getSut(
            mediaResource: $this->createConfiguredStub(MediaResourceInterface::class, [
                'getPossibleMediaFilePath' => new FilePath($targetPath),
            ]),
            fileSystemService: $fileSystemSpy,
            fallbackMediaResource: $this->createConfiguredStub(FallbackMediaResourceInterface::class, [
                'getSourcePath' => $sourcePath,
                'getFileName' => $fileName,
            ]),
        );

        $sut->seedMedia();
    }

    #[Test]
    public function seedMediaAddsTheMediaRecordWithTheFixedFallbackMediaId(): void
    {
        $mediaId = uniqid();
        $fileName = uniqid() . '.jpg';
        $fileSize = rand(1, 100000);
        $mimeType = 'image/jpeg';

        $mediaRepositorySpy = $this->createMock(MediaRepositoryInterface::class);
        $mediaRepositorySpy->method('getMediaById')->willThrowException(new MediaNotFoundException());
        $mediaRepositorySpy->expects($this->once())
            ->method('addMedia')
            ->with($this->callback(
                fn(MediaInterface $media): bool => $media->getOxid() === $mediaId
                    && $media->getFileName() === $fileName
                    && $media->getFileSize() === $fileSize
                    && $media->getFileType() === $mimeType
                    && $media->getFolderId() === ''
            ));

        $sut = $this->getSut(
            mediaRepository: $mediaRepositorySpy,
            mediaResource: $this->createConfiguredStub(MediaResourceInterface::class, [
                'getPossibleMediaFilePath' => new FilePath('/shop/out/pictures/ddmedia/' . $fileName),
            ]),
            fileSystemService: $this->createConfiguredStub(FileSystemServiceInterface::class, [
                'getFileSize' => $fileSize,
                'getMimeType' => $mimeType,
                'getImageSize' => new ImageSize(rand(1, 500), rand(1, 500)),
            ]),
            fallbackMediaResource: $this->createConfiguredStub(FallbackMediaResourceInterface::class, [
                'getMediaId' => $mediaId,
            ]),
        );

        $sut->seedMedia();
    }

    #[Test]
    public function seedMediaWritesTheFallbackMediaIdIntoTheEmptySetting(): void
    {
        $mediaId = uniqid();

        $settingsSpy = $this->createMock(FallbackMediaSettingsInterface::class);
        $settingsSpy->method('getFallbackMediaId')->willReturn('');
        $settingsSpy->expects($this->once())
            ->method('saveFallbackMediaId')
            ->with($mediaId);

        $sut = $this->getSut(
            fallbackMediaSettings: $settingsSpy,
            fallbackMediaResource: $this->createConfiguredStub(FallbackMediaResourceInterface::class, [
                'getMediaId' => $mediaId,
            ]),
        );

        $sut->seedMedia();
    }

    #[Test]
    public function seedMediaDoesNotAddTheMediaAgainWhenTheRecordAlreadyExists(): void
    {
        $mediaId = uniqid();

        $mediaRepositorySpy = $this->createMock(MediaRepositoryInterface::class);
        $mediaRepositorySpy->method('getMediaById')
            ->with($mediaId)
            ->willReturn($this->createStub(MediaInterface::class));
        $mediaRepositorySpy->expects($this->never())->method('addMedia');

        $fileSystemSpy = $this->createMock(FileSystemServiceInterface::class);
        $fileSystemSpy->expects($this->never())->method('copy');

        $settingsSpy = $this->createMock(FallbackMediaSettingsInterface::class);
        $settingsSpy->method('getFallbackMediaId')->willReturn('');
        $settingsSpy->expects($this->once())
            ->method('saveFallbackMediaId')
            ->with($mediaId);

        $sut = $this->getSut(
            mediaRepository: $mediaRepositorySpy,
            fileSystemService: $fileSystemSpy,
            fallbackMediaSettings: $settingsSpy,
            fallbackMediaResource: $this->createConfiguredStub(FallbackMediaResourceInterface::class, [
                'getMediaId' => $mediaId,
            ]),
        );

        $sut->seedMedia();
    }

    #[Test]
    public function seedMediaKeepsAFallbackMediaIdConfiguredByTheAdmin(): void
    {
        $settingsSpy = $this->createMock(FallbackMediaSettingsInterface::class);
        $settingsSpy->method('getFallbackMediaId')->willReturn(uniqid());
        $settingsSpy->expects($this->never())->method('saveFallbackMediaId');

        $sut = $this->getSut(fallbackMediaSettings: $settingsSpy);

        $sut->seedMedia();
    }

    private function getSut(
        ?MediaRepositoryInterface $mediaRepository = null,
        ?MediaResourceInterface $mediaResource = null,
        ?FileSystemServiceInterface $fileSystemService = null,
        ?FallbackMediaSettingsInterface $fallbackMediaSettings = null,
        ?FallbackMediaResourceInterface $fallbackMediaResource = null,
    ): FallbackMediaSeeder {
        $mediaRepository ??= $this->createMediaNotFoundRepositoryStub();
        $mediaResource ??= $this->createConfiguredStub(MediaResourceInterface::class, [
            'getPossibleMediaFilePath' => new FilePath('/shop/out/pictures/ddmedia/nopic.jpg'),
        ]);
        $fileSystemService ??= $this->createConfiguredStub(FileSystemServiceInterface::class, [
            'getImageSize' => new ImageSize(rand(1, 500), rand(1, 500)),
        ]);
        $fallbackMediaSettings ??= $this->createConfiguredStub(FallbackMediaSettingsInterface::class, [
            'getFallbackMediaId' => '',
        ]);
        $fallbackMediaResource ??= $this->createStub(FallbackMediaResourceInterface::class);

        return new FallbackMediaSeeder(
            mediaRepository: $mediaRepository,
            mediaResource: $mediaResource,
            fileSystemService: $fileSystemService,
            fallbackMediaSettings: $fallbackMediaSettings,
            fallbackMediaResource: $fallbackMediaResource,
        );
    }

    private function createMediaNotFoundRepositoryStub(): MediaRepositoryInterface&Stub
    {
        $mediaRepository = $this->createStub(MediaRepositoryInterface::class);
        $mediaRepository->method('getMediaById')->willThrowException(new MediaNotFoundException());

        return $mediaRepository;
    }
}
