<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Breadcrumb\Service;

use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\MediaLibrary\Breadcrumb\DataType\BreadcrumbInterface;
use OxidEsales\MediaLibrary\Breadcrumb\Service\BreadcrumbService;
use OxidEsales\MediaLibrary\Breadcrumb\Service\BreadcrumbServiceInterface;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BreadcrumbService::class)]
class BreadcrumbServiceTest extends TestCase
{
    public function testEmptyFolderId(): void
    {
        $rootTranslation = uniqid('rootTranslation');
        $shopAdapterMock = $this->createStub(ShopAdapterInterface::class);
        $shopAdapterMock->method('translateString')
            ->willReturnMap([
                ['DD_MEDIA_BREADCRUMB_ROOT', $rootTranslation],
            ]);

        $sut = $this->getSut(
            shopAdapter: $shopAdapterMock,
        );

        $result = $sut->getBreadcrumbs(folderId: '');

        $this->assertSame(1, count($result));

        /** @var BreadcrumbInterface $breadcrumb */
        $breadcrumb = array_shift($result);
        $this->assertSame($rootTranslation, $breadcrumb->getName());
        $this->assertTrue($breadcrumb->isActive());
    }

    public function testWithFolderId(): void
    {
        $folderMediaStub = $this->createConfiguredStub(MediaInterface::class, [
            'getFileName' => $folderName = uniqid('folderName'),
        ]);

        $folderId = uniqid('folderId');
        $mediaRepositoryMock = $this->createMock(MediaRepositoryInterface::class);
        $mediaRepositoryMock->method('getMediaById')
            ->with($folderId)
            ->willReturn($folderMediaStub);

        $rootTranslation = uniqid('rootTranslation');
        $shopAdapterMock = $this->createStub(ShopAdapterInterface::class);
        $shopAdapterMock->method('translateString')
            ->willReturnMap([
                ['DD_MEDIA_BREADCRUMB_ROOT', $rootTranslation],
            ]);

        $sut = $this->getSut(
            mediaRepository: $mediaRepositoryMock,
            shopAdapter: $shopAdapterMock,
        );

        $result = $sut->getBreadcrumbs($folderId);

        $this->assertSame(2, count($result));

        $breadcrumb = array_shift($result);
        $this->assertSame($rootTranslation, $breadcrumb->getName());
        $this->assertFalse($breadcrumb->isActive());

        $breadcrumb = array_shift($result);
        $this->assertSame($folderName, $breadcrumb->getName());
        $this->assertTrue($breadcrumb->isActive());
    }

    private function getSut(
        ?MediaRepositoryInterface $mediaRepository = null,
        ?ShopAdapterInterface $shopAdapter = null,
    ): BreadcrumbServiceInterface {
        $mediaRepository ??= $this->createStub(MediaRepositoryInterface::class);
        $shopAdapter ??= $this->createStub(ShopAdapterInterface::class);

        return new BreadcrumbService(
            mediaRepository: $mediaRepository,
            shopAdapter: $shopAdapter,
        );
    }
}
