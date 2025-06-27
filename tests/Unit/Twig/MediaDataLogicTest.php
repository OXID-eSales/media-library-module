<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Twig;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Repository\PreloadMediaRepositoryInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaObjectResourceInterface;
use OxidEsales\MediaLibrary\Twig\MediaDataLogic;
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

        $result = $sut->getMediaUrl($mediaId);
        $this->assertSame($expectedUrl, $result);
    }
}
