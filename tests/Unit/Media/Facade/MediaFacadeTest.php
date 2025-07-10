<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Facade;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Facade\MediaFacade;
use OxidEsales\MediaLibrary\Media\Facade\MediaFacadeInterface;
use OxidEsales\MediaLibrary\Media\Repository\PreloadMediaRepositoryInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaObjectResourceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MediaFacadeTest extends TestCase
{
    #[Test]
    public function registerForPreloadTriggersRepositoryMethod(): void
    {
        $ids = [uniqid(), uniqid()];

        $preloadRepositorySpy = $this->createMock(PreloadMediaRepositoryInterface::class);
        $preloadRepositorySpy->expects($this->once())
            ->method('registerForPreload')
            ->with(...$ids);

        $sut = $this->getSut(
            preloadMediaRepository: $preloadRepositorySpy,
        );

        $sut->registerForPreload(...$ids);
    }

    #[Test]
    public function getsMediaFromRepository(): void
    {
        $id = uniqid();

        $repositoryMock = $this->createMock(PreloadMediaRepositoryInterface::class);
        $repositoryMock->method('getMediaById')
            ->with($id)
            ->willReturn($mediaStub = $this->createStub(MediaInterface::class));

        $sut = $this->getSut(
            preloadMediaRepository: $repositoryMock,
        );

        $result = $sut->getMedia($id);

        $this->assertSame($mediaStub, $result);
    }

    #[Test]
    public function calculatesTheUrlFromRepositoryMedia(): void
    {
        $id = uniqid();

        $repositoryMock = $this->createMock(PreloadMediaRepositoryInterface::class);
        $repositoryMock->method('getMediaById')
            ->with($id)
            ->willReturn($mediaStub = $this->createStub(MediaInterface::class));

        $mediaObjectResourceMock = $this->createMock(MediaObjectResourceInterface::class);
        $mediaObjectResourceMock->method('getUrlToMedia')
            ->with($mediaStub)
            ->willReturn($exampleUrl = uniqid());

        $sut = $this->getSut(
            preloadMediaRepository: $repositoryMock,
            mediaObjectResource: $mediaObjectResourceMock,
        );

        $result = $sut->getMediaUrl($id);

        $this->assertSame($exampleUrl, $result);
    }

    private function getSut(
        ?PreloadMediaRepositoryInterface $preloadMediaRepository = null,
        ?MediaObjectResourceInterface $mediaObjectResource = null,
    ): MediaFacadeInterface {
        $preloadMediaRepository ??= $this->createStub(PreloadMediaRepositoryInterface::class);
        $mediaObjectResource ??= $this->createStub(MediaObjectResourceInterface::class);

        return new MediaFacade(
            preloadMediaRepository: $preloadMediaRepository,
            mediaObjectResource: $mediaObjectResource,
        );
    }
}
