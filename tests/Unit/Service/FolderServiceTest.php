<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Service;

use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\MediaLibrary\Image\Service\ImageResourceRefactoredInterface;
use OxidEsales\MediaLibrary\Media\DataType\Media as MediaDataType;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Service\FileSystemServiceInterface;
use OxidEsales\MediaLibrary\Service\FolderService;
use OxidEsales\MediaLibrary\Service\NamingServiceInterface;
use PHPUnit\Framework\TestCase;

class FolderServiceTest extends TestCase
{
    public function testCreateCustomDir(): void
    {
        $sut = new FolderService(
            $imageResourceStub = $this->createStub(ImageResourceRefactoredInterface::class),
            $namingServiceMock = $this->createStub(NamingServiceInterface::class),
            $mediaRepositorySpy = $this->createMock(MediaRepositoryInterface::class),
            $fileSystemServiceSpy = $this->createMock(FileSystemServiceInterface::class),
            $shopAdapterStub = $this->createStub(ShopAdapterInterface::class),
        );

        $uniqueId = 'someUniqueId';
        $shopAdapterStub->method('generateUniqueId')->willReturn($uniqueId);

        $newFolderName = 'inputName';
        $sanitizedFolderName = 'sanitizedName';
        $namingServiceMock->method('sanitizeFilename')->with($newFolderName)->willReturn($sanitizedFolderName);

        $fullMediaPath = 'someMediaPath';
        $uniqueMediaPath = 'someUniqueMediaPath';
        $imageResourceStub->method('getPathToMediaFiles')->with($sanitizedFolderName)->willReturn($fullMediaPath);
        $namingServiceMock->method('getUniqueFilename')->willReturn($fullMediaPath)->willReturn($uniqueMediaPath);

        $fileSystemServiceSpy->expects($this->once())->method('ensureDirectory')->with($uniqueMediaPath);

        $newMediaItem = new MediaDataType(
            oxid: $uniqueId,
            fileName: $uniqueMediaPath,
            fileType: 'directory'
        );

        $mediaRepositorySpy->expects($this->once())->method('addMedia')->with($newMediaItem);

        $this->assertEquals($newMediaItem, $sut->createCustomDir($newFolderName));
    }
}
