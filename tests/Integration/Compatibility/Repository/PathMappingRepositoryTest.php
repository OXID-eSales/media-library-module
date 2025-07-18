<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Integration\Compatibility\Repository;

use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\MediaLibrary\Compatibility\DTO\MediaFileInformationInterface;
use OxidEsales\MediaLibrary\Compatibility\Exception\MediaNotFoundByFileInformationException;
use OxidEsales\MediaLibrary\Compatibility\Repository\PathMappingRepository;
use OxidEsales\MediaLibrary\Media\DataType\Media;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

class PathMappingRepositoryTest extends IntegrationTestCase
{
    #[Test]
    public function mediaIdIsFoundByPath(): void
    {
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

        $sut = new PathMappingRepository(
            queryBuilderFactory: $this->get(QueryBuilderFactoryInterface::class),
        );

        $this->assertEquals($oxid, $sut->getMediaIdByInformation($mediaFileInformationStub));
    }

    #[Test]
    public function exceptionThrownOnMediaNotFoundByPath(): void
    {
        $fileInformation = $this->createConfiguredStub(MediaFileInformationInterface::class, [
            'getFileName' => uniqid(),
            'getFolderName' => uniqid(),
        ]);

        $sut = new PathMappingRepository(
            queryBuilderFactory: $this->get(QueryBuilderFactoryInterface::class),
        );

        $this->expectException(MediaNotFoundByFileInformationException::class);
        $sut->getMediaIdByInformation($fileInformation);
    }
}
