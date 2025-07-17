<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Integration\Compatibility\Repository;

use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\MediaLibrary\Compatibility\DTO\MediaFileInformationInterface;
use OxidEsales\MediaLibrary\Compatibility\Exception\MediaByPathNotFoundException;
use OxidEsales\MediaLibrary\Compatibility\Repository\PathMappingRepository;
use OxidEsales\MediaLibrary\Compatibility\Service\MediaPathServiceInterface;
use OxidEsales\MediaLibrary\Media\DataType\Media;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

class PathMappingRepositoryTest extends IntegrationTestCase
{
    #[Test]
    public function mediaIdIsFoundByPath(): void
    {
        $path = uniqid();

        $mediaRepository = $this->get(MediaRepositoryInterface::class);
        $mediaRepository->addMedia(
            new Media(
                oxid: $folderOxid = uniqid(),
                fileName: $folderName = uniqid(),
            )
        );
        $mediaRepository->addMedia(
            new Media(
                oxid: $oxid = uniqid(),
                fileName: $fileName = uniqid(),
                folderId: $folderOxid,
            )
        );

        $mediaFileInformationStub = $this->createConfiguredStub(MediaFileInformationInterface::class, [
            'getFileName' => $fileName,
            'getFolderName' => $folderName,
        ]);

        $pathServiceMock = $this->createMock(MediaPathServiceInterface::class);
        $pathServiceMock->method('getMediaFileInformation')
            ->with($path)
            ->willReturn($mediaFileInformationStub);

        $sut = new PathMappingRepository(
            mediaPathService: $pathServiceMock,
            queryBuilderFactory: $this->get(QueryBuilderFactoryInterface::class),
        );

        $this->assertEquals($oxid, $sut->getMediaIdByPath($path));
    }

    #[Test]
    public function exceptionThrownOnMediaNotFoundByPath(): void
    {
        $pathServiceMock = $this->createMock(MediaPathServiceInterface::class);
        $pathServiceMock->method('getMediaFileInformation')
            ->willReturn(
                $this->createConfiguredStub(MediaFileInformationInterface::class, [
                    'getFileName' => uniqid(),
                    'getFolderName' => uniqid(),
                ])
            );

        $sut = new PathMappingRepository(
            mediaPathService: $pathServiceMock,
            queryBuilderFactory: $this->get(QueryBuilderFactoryInterface::class),
        );

        $this->expectException(MediaByPathNotFoundException::class);
        $sut->getMediaIdByPath(uniqid());
    }
}
