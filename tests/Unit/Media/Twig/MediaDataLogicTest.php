<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Twig;

use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\Facade\MediaFacadeInterface;
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

        $mediaFacadeMock = $this->createMock(MediaFacadeInterface::class);
        $mediaFacadeMock->method('getMediaUrl')
            ->with($mediaId)
            ->willReturn($expectedUrl = uniqid());

        $sut = $this->getSut(
            mediaFacade: $mediaFacadeMock,
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

    private function getSut(
        MediaFacadeInterface $mediaFacade = null,
    ): MediaDataLogicInterface {
        return new MediaDataLogic(
            mediaFacade: $mediaFacade ?? $this->createStub(MediaFacadeInterface::class),
        );
    }
}
