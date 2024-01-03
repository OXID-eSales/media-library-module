<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Breadcrumb\Service;

use OxidEsales\MediaLibrary\Breadcrumb\DataType\BreadcrumbInterface;
use OxidEsales\MediaLibrary\Breadcrumb\Service\BreadcrumbService;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Transput\RequestData\UIRequestInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Breadcrumb\Service\BreadcrumbService
 */
class BreadcrumbServiceTest extends TestCase
{
    public function testNoFolderId(): void
    {
        $sut = $this->getSutWithoutFolderIdInRequest();

        $result = $sut->getBreadcrumbsByRequest();

        $this->assertSame(1, count($result));

        /** @var BreadcrumbInterface $breadcrumb */
        $breadcrumb = array_shift($result);
        $this->assertSame('Root', $breadcrumb->getName());
        $this->assertTrue($breadcrumb->isActive());
    }

    public function testWithFolderId(): void
    {
        $sut = $this->getSutWithFolderIdInRequestPreconfigured();

        $result = $sut->getBreadcrumbsByRequest();

        $this->assertSame(2, count($result));

        /** @var BreadcrumbInterface $breadcrumb */
        $breadcrumb = array_shift($result);
        $this->assertSame('Root', $breadcrumb->getName());
        $this->assertFalse($breadcrumb->isActive());

        /** @var BreadcrumbInterface $breadcrumb */
        $breadcrumb = array_shift($result);
        $this->assertSame('someMediaName', $breadcrumb->getName());
        $this->assertTrue($breadcrumb->isActive());
    }

    private function getSutWithFolderIdInRequestPreconfigured(): BreadcrumbService
    {
        $requestStub = $this->createMock(UIRequestInterface::class);
        $requestStub->method('getFolderId')->willReturn('someFolderId');

        $exampleMedia = $this->createMock(MediaInterface::class);
        $exampleMedia->method('getFileName')->willReturn('someMediaName');

        $mediaRepository = $this->createMock(MediaRepositoryInterface::class);
        $mediaRepository->method('getMediaById')->with('someFolderId')->willReturn($exampleMedia);

        $sut = new BreadcrumbService(
            request: $requestStub,
            mediaRepository: $mediaRepository
        );

        return $sut;
    }

    private function getSutWithoutFolderIdInRequest(): BreadcrumbService
    {
        $requestStub = $this->createStub(UIRequestInterface::class);
        $mediaRepository = $this->createStub(MediaRepositoryInterface::class);

        $sut = new BreadcrumbService(
            request: $requestStub,
            mediaRepository: $mediaRepository
        );

        return $sut;
    }
}
