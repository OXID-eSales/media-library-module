<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Twig;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\Repository\PreloadMediaRepositoryInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaObjectResourceInterface;
use OxidEsales\MediaLibrary\Media\Twig\MediaDataLogic;
use OxidEsales\MediaLibrary\Media\Twig\MediaDataLogicInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MediaDataLogicTest extends TestCase
{
    #[Test]
    public function mediaAccessedByMediaIdAndUrlReturned(): void
    {
        $mediaId = uniqid();

        $mediaStub = $this->createStub(MediaInterface::class);
        $mediaRepositoryMock = $this->createMock(PreloadMediaRepositoryInterface::class);
        $mediaRepositoryMock->method('getMediaById')
            ->with($mediaId)
            ->willReturn($mediaStub);

        $mediaObjectResourceMock = $this->createMock(MediaObjectResourceInterface::class);
        $mediaObjectResourceMock->method('getUrlToMedia')
            ->with($mediaStub)
            ->willReturn($expectedUrl = uniqid());

        $sut = new MediaDataLogic(
            mediaRepository: $mediaRepositoryMock,
            mediaObjectResource: $mediaObjectResourceMock
        );

        $sut = $this->getSut(
            preloadMediaRepository: $mediaRepositoryMock,
            mediaObjectResource: $mediaObjectResourceMock,
        );

        $result = $sut->getMediaUrl($mediaId);
        $this->assertSame($expectedUrl, $result);
    }

    #[Test]
    public function giveEmptyStringAsUrlIfMediaDoesntExist(): void
    {
        $mediaId = uniqid();

        $mediaRepositoryMock = $this->createMock(PreloadMediaRepositoryInterface::class);
        $mediaRepositoryMock->method('getMediaById')
            ->with($mediaId)
            ->willThrowException(new MediaNotFoundException());

        $sut = $this->getSut(
            preloadMediaRepository: $mediaRepositoryMock,
        );

        $result = $sut->getMediaUrl($mediaId);
        $this->assertSame('', $result);
    }

    private function getSut(
        PreloadMediaRepositoryInterface $preloadMediaRepository = null,
        MediaObjectResourceInterface $mediaObjectResource = null,
    ): MediaDataLogicInterface {
        return new MediaDataLogic(
            mediaRepository: $preloadMediaRepository ?? $this->createStub(PreloadMediaRepositoryInterface::class),
            mediaObjectResource: $mediaObjectResource ?? $this->createStub(MediaObjectResourceInterface::class),
        );
    }
}
