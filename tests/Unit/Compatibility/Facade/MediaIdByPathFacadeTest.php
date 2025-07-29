<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Unit\Compatibility\Facade;

use OxidEsales\MediaLibrary\Compatibility\DTO\MediaFileInformationInterface;
use OxidEsales\MediaLibrary\Compatibility\Exception\MediaNotFoundByFileInformationException;
use OxidEsales\MediaLibrary\Compatibility\Facade\MediaIdByPathFacade;
use OxidEsales\MediaLibrary\Compatibility\Facade\MediaIdByPathFacadeInterface;
use OxidEsales\MediaLibrary\Compatibility\Factory\MediaFileInformationFactory;
use OxidEsales\MediaLibrary\Compatibility\Factory\MediaFileInformationFactoryInterface;
use OxidEsales\MediaLibrary\Compatibility\Repository\PathMappingRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MediaIdByPathFacadeTest extends TestCase
{
    #[Test]
    public function getMediaIdByPathReturnMediaIdIfOneFoundByRepository(): void
    {
        $examplePath = uniqid();

        $fileInformationFactoryMock = $this->createMock(MediaFileInformationFactoryInterface::class);
        $fileInformationFactoryMock->method('fromPath')
            ->with($examplePath)
            ->willReturn($fileInformationStub = $this->createStub(MediaFileInformationInterface::class));

        $pathMappingRepositoryMock = $this->createMock(PathMappingRepositoryInterface::class);
        $pathMappingRepositoryMock->method('getMediaIdByInformation')
            ->with($fileInformationStub)
            ->willReturn($mediaId = uniqid());

        $sut = $this->getSut(
            fileInformationFactory: $fileInformationFactoryMock,
            pathMappingRepository: $pathMappingRepositoryMock
        );

        $this->assertSame($mediaId, $sut->getMediaIdByPath($examplePath));
    }

    #[Test]
    public function getMediaIdByPathExplodesWithExpectedExceptionIfMediaNotFoundByRepository(): void
    {
        $pathMappingRepositoryMock = $this->createMock(PathMappingRepositoryInterface::class);
        $pathMappingRepositoryMock->method('getMediaIdByInformation')
            ->willThrowException(new MediaNotFoundByFileInformationException());

        $sut = $this->getSut(
            pathMappingRepository: $pathMappingRepositoryMock
        );

        $this->expectException(MediaNotFoundByFileInformationException::class);
        $sut->getMediaIdByPath(uniqid());
    }

    public function getSut(
        ?MediaFileInformationFactoryInterface $fileInformationFactory = null,
        ?PathMappingRepositoryInterface $pathMappingRepository = null,
    ): MediaIdByPathFacadeInterface {
        $fileInformationFactory ??= $this->createStub(MediaFileInformationFactory::class);
        $pathMappingRepository ??= $this->createStub(PathMappingRepositoryInterface::class);

        return new MediaIdByPathFacade(
            mediaFileInformationFactory: $fileInformationFactory,
            pathMappingRepository: $pathMappingRepository,
        );
    }
}
