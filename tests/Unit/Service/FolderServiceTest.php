<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Service;

use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\MediaLibrary\Media\DataType\Media as MediaDataType;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaResourceInterface;
use OxidEsales\MediaLibrary\Service\FileSystemServiceInterface;
use OxidEsales\MediaLibrary\Service\FolderService;
use OxidEsales\MediaLibrary\Service\FolderServiceInterface;
use OxidEsales\MediaLibrary\Service\NamingServiceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FolderService::class)]
class FolderServiceTest extends TestCase
{
    #[Test]
    public function createCustomDir(): void
    {
        $uniqueId = 'someUniqueId';
        $shopAdapterMock = $this->createStub(ShopAdapterInterface::class);
        $shopAdapterMock->method('generateUniqueId')
            ->willReturn($uniqueId);

        $sanitizedFolderName = 'sanitizedName';
        $fullMediaPath = 'someMediaPath';
        $imageResourceMock = $this->createStub(MediaResourceInterface::class);
        $imageResourceMock->method('getPathToMediaFiles')
            ->with($sanitizedFolderName)
            ->willReturn($fullMediaPath);

        $newFolderName = 'inputName';
        $uniqueMediaPath = 'someUniqueMediaPath';
        $namingServiceMock = $this->createStub(NamingServiceInterface::class);
        $namingServiceMock->method('sanitizeFilename')
            ->with($newFolderName)
            ->willReturn($sanitizedFolderName);
        $namingServiceMock->method('getUniqueFilename')
            ->with($fullMediaPath)
            ->willReturn($uniqueMediaPath);

        $fileSystemServiceSpy = $this->createMock(FileSystemServiceInterface::class);
        $fileSystemServiceSpy->expects($this->once())
            ->method('ensureDirectory')
            ->with($uniqueMediaPath);

        $newMediaItem = new MediaDataType(
            oxid: $uniqueId,
            fileName: $uniqueMediaPath,
            fileType: 'directory'
        );

        $mediaRepositorySpy = $this->createMock(MediaRepositoryInterface::class);
        $mediaRepositorySpy->expects($this->once())
            ->method('addMedia')
            ->with($newMediaItem);

        $sut = $this->getSut(
            mediaResource: $imageResourceMock,
            namingService: $namingServiceMock,
            mediaRepository: $mediaRepositorySpy,
            fileSystemService: $fileSystemServiceSpy,
            shopAdapter: $shopAdapterMock,
        );

        $this->assertEquals($newMediaItem, $sut->createCustomDir($newFolderName));
    }

    private function getSut(
        ?MediaResourceInterface $mediaResource = null,
        ?NamingServiceInterface $namingService = null,
        ?MediaRepositoryInterface $mediaRepository = null,
        ?FileSystemServiceInterface $fileSystemService = null,
        ?ShopAdapterInterface $shopAdapter = null,
    ): FolderServiceInterface {
        $mediaResource ??= $this->createStub(MediaResourceInterface::class);
        $namingService ??= $this->createStub(NamingServiceInterface::class);
        $mediaRepository ??= $this->createStub(MediaRepositoryInterface::class);
        $fileSystemService ??= $this->createStub(FileSystemServiceInterface::class);
        $shopAdapter ??= $this->createStub(ShopAdapterInterface::class);

        return new FolderService(
            mediaResource: $mediaResource,
            namingService: $namingService,
            mediaRepository: $mediaRepository,
            fileSystemService: $fileSystemService,
            shopAdapter: $shopAdapter,
        );
    }
}
