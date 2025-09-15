<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Twig;

use OxidEsales\MediaLibrary\Media\DataType\MediaAltTextInterface;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\Exception\MediaAltTextNotFoundException;
use OxidEsales\MediaLibrary\Media\Facade\MediaFacadeInterface;
use OxidEsales\MediaLibrary\Media\Repository\MediaAltRepositoryInterface;
use OxidEsales\MediaLibrary\Media\Twig\MediaDataLogic;
use OxidEsales\MediaLibrary\Media\Twig\MediaDataLogicInterface;
use OxidEsales\MediaLibrary\Media\DataType\MediaAltText;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MediaDataLogicTest extends TestCase
{
    #[Test]
    public function mediaAccessedByMediaIdAndUrlReturned(): void
    {
        $mediaId = uniqid();

        $mediaFacadStub = $this->createMock(MediaFacadeInterface::class);
        $mediaFacadStub->method('getMediaUrl')
            ->with($mediaId)
            ->willReturn($expectedUrl = uniqid());

        $sut = $this->getSut(
            mediaFacade: $mediaFacadStub,
        );

        $result = $sut->getMediaUrl($mediaId);
        $this->assertSame($expectedUrl, $result);
    }

    #[Test]
    public function giveEmptyStringAsUrlIfMediaDoesntExist(): void
    {
        $mediaId = uniqid();

        $mediaFacadeMock = $this->createMock(MediaFacadeInterface::class);
        $mediaFacadeMock->method('getMediaUrl')
            ->with($mediaId)
            ->willThrowException(new MediaNotFoundException());

        $sut = $this->getSut(
            mediaFacade: $mediaFacadeMock,
        );

        $result = $sut->getMediaUrl($mediaId);
        $this->assertSame('', $result);
    }

    #[Test]
    public function getMediaAltTextReturnsCorrectText(): void
    {
        $objectId = uniqid();
        $expectedText = uniqid();

        $mediaMock = $this->createMock(MediaInterface::class);
        $mediaMock->method('getMediaAltText')->willReturn($expectedText);

        $mediaFacadeMock = $this->createMock(MediaFacadeInterface::class);
        $mediaFacadeMock->method('getMedia')->with($objectId)->willReturn($mediaMock);

        $sut = $this->getSut(mediaFacade: $mediaFacadeMock);
        $result = $sut->getMediaAltText($objectId, 1);
        $this->assertSame($expectedText, $result);
    }

    #[Test]
    public function getMediaAltTextReturnsEmptyStringOnException(): void
    {
        $objectId = uniqid();
        $mediaFacadeMock = $this->createMock(MediaFacadeInterface::class);
        $mediaFacadeMock->method('getMedia')->with($objectId)->willThrowException(new MediaNotFoundException());

        $sut = $this->getSut(mediaFacade: $mediaFacadeMock);
        $result = $sut->getMediaAltText($objectId, 1);
        $this->assertSame('', $result);
    }

    private function getSut(
        ?MediaFacadeInterface $mediaFacade = null
    ): MediaDataLogicInterface {
        return new MediaDataLogic(
            mediaFacade: $mediaFacade ?? $this->createStub(MediaFacadeInterface::class),
        );
    }
}
