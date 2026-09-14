<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Facade;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\DataType\MediaLookupContextInterface;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\Facade\FallbackMediaFacadeDecorator;
use OxidEsales\MediaLibrary\Media\Facade\MediaFacadeInterface;
use OxidEsales\MediaLibrary\Media\Settings\FallbackMediaSettingsInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FallbackMediaFacadeDecoratorTest extends TestCase
{
    #[Test]
    public function getMediaReturnsOriginalResultIfItsFine(): void
    {
        $originalFacadeMock = $this->createMock(MediaFacadeInterface::class);

        $exampleMediaId = uniqid('mediaId');
        $contextStub = $this->createStub(MediaLookupContextInterface::class);
        $originalFacadeMock->method('getMedia')
            ->with($exampleMediaId, $contextStub)
            ->willReturn($mediaStub = $this->createStub(MediaInterface::class));

        $sut = $this->getSut(
            originalMediaFacade: $originalFacadeMock,
        );

        $result = $sut->getMedia($exampleMediaId, $contextStub);
        $this->assertSame($mediaStub, $result);
    }

    #[Test]
    public function getMediaUrlReturnsOriginalResultIfItsFine()
    {
        $originalFacadeMock = $this->createMock(MediaFacadeInterface::class);

        $exampleMediaId = uniqid('mediaId');
        $contextStub = $this->createStub(MediaLookupContextInterface::class);
        $originalFacadeMock->method('getMediaUrl')
            ->with($exampleMediaId, $contextStub)
            ->willReturn($originalMediaUrl = uniqid('mediaUrl'));

        $sut = $this->getSut(
            originalMediaFacade: $originalFacadeMock,
        );

        $result = $sut->getMediaUrl($exampleMediaId, $contextStub);
        $this->assertSame($originalMediaUrl, $result);
    }

    #[Test]
    public function getMediaReturnsFallbackResultIfOriginalMediaNotFound(): void
    {
        $exampleMediaId = uniqid('mediaId');
        $settingsStub = $this->createConfiguredStub(FallbackMediaSettingsInterface::class, [
            'getFallbackMediaId' => $fallbackMediaId = uniqid('fallbackMediaId'),
        ]);

        $contextStub = $this->createStub(MediaLookupContextInterface::class);
        $fallbackMediaStub = $this->createStub(MediaInterface::class);
        $originalFacadeMock = $this->createMock(MediaFacadeInterface::class);
        $originalFacadeMock->method('getMedia')
            ->willReturnCallback(function (
                string $mediaId,
                ?MediaLookupContextInterface $context
            ) use (
                $contextStub,
                $fallbackMediaId,
                $fallbackMediaStub,
            ) {
                $this->assertSame($contextStub, $context);
                if ($mediaId === $fallbackMediaId) {
                    return $fallbackMediaStub;
                }
                throw new MediaNotFoundException();
            });

        $sut = $this->getSut(
            originalMediaFacade: $originalFacadeMock,
            fallbackMediaSettings: $settingsStub,
        );

        $result = $sut->getMedia($exampleMediaId, $contextStub);
        $this->assertSame($fallbackMediaStub, $result);
    }

    #[Test]
    public function getMediaUrlReturnsFallbackResultIfOriginalMediaNotFound(): void
    {
        $exampleMediaId = uniqid('mediaId');
        $settingsStub = $this->createConfiguredStub(FallbackMediaSettingsInterface::class, [
            'getFallbackMediaId' => $fallbackMediaId = uniqid('fallbackMediaId'),
        ]);

        $contextStub = $this->createStub(MediaLookupContextInterface::class);
        $fallbackMediaUrl = uniqid('fallbackMediaUrl');
        $originalFacadeMock = $this->createMock(MediaFacadeInterface::class);
        $originalFacadeMock->method('getMediaUrl')
            ->willReturnCallback(function (
                string $mediaId,
                ?MediaLookupContextInterface $context
            ) use (
                $contextStub,
                $fallbackMediaId,
                $fallbackMediaUrl,
            ) {
                $this->assertSame($contextStub, $context);
                if ($mediaId === $fallbackMediaId) {
                    return $fallbackMediaUrl;
                }
                throw new MediaNotFoundException();
            });

        $sut = $this->getSut(
            originalMediaFacade: $originalFacadeMock,
            fallbackMediaSettings: $settingsStub,
        );

        $result = $sut->getMediaUrl($exampleMediaId, $contextStub);
        $this->assertSame($fallbackMediaUrl, $result);
    }

    #[Test]
    public function getMediaThrowsExceptionIfOriginalAndFallbackMediaNotFound(): void
    {
        $exampleMediaId = uniqid('mediaId');
        $settingsStub = $this->createConfiguredStub(FallbackMediaSettingsInterface::class, [
            'getFallbackMediaId' => uniqid('fallbackMediaId'),
        ]);

        $originalFacadeMock = $this->createMock(MediaFacadeInterface::class);
        $originalFacadeMock->expects($this->exactly(2))
            ->method('getMedia')
            ->willThrowException(new MediaNotFoundException());

        $sut = $this->getSut(
            originalMediaFacade: $originalFacadeMock,
            fallbackMediaSettings: $settingsStub,
        );

        $this->expectException(MediaNotFoundException::class);
        $sut->getMedia($exampleMediaId);
    }

    #[Test]
    public function getMediaUrlThrowsExceptionIfOriginalAndFallbackMediaNotFound(): void
    {
        $exampleMediaId = uniqid('mediaId');
        $settingsStub = $this->createConfiguredStub(FallbackMediaSettingsInterface::class, [
            'getFallbackMediaId' => uniqid('fallbackMediaId'),
        ]);

        $originalFacadeMock = $this->createMock(MediaFacadeInterface::class);
        $originalFacadeMock->expects($this->exactly(2))
            ->method('getMediaUrl')
            ->willThrowException(new MediaNotFoundException());

        $sut = $this->getSut(
            originalMediaFacade: $originalFacadeMock,
            fallbackMediaSettings: $settingsStub,
        );

        $this->expectException(MediaNotFoundException::class);
        $sut->getMediaUrl($exampleMediaId);
    }

    #[Test]
    public function getMediaRethrowsOriginalExceptionIfNoFallbackConfigured(): void
    {
        $exampleMediaId = uniqid('mediaId');
        $settingsStub = $this->createConfiguredStub(FallbackMediaSettingsInterface::class, [
            'getFallbackMediaId' => '',
        ]);

        $originalFacadeMock = $this->createMock(MediaFacadeInterface::class);
        $originalFacadeMock->expects($this->once())
            ->method('getMedia')
            ->with($exampleMediaId)
            ->willThrowException($exception = new MediaNotFoundException());

        $sut = $this->getSut(
            originalMediaFacade: $originalFacadeMock,
            fallbackMediaSettings: $settingsStub,
        );

        $this->expectExceptionObject($exception);
        $sut->getMedia($exampleMediaId);
    }

    #[Test]
    public function getMediaUrlRethrowsOriginalExceptionIfNoFallbackConfigured(): void
    {
        $exampleMediaId = uniqid('mediaId');
        $settingsStub = $this->createConfiguredStub(FallbackMediaSettingsInterface::class, [
            'getFallbackMediaId' => '',
        ]);

        $originalFacadeMock = $this->createMock(MediaFacadeInterface::class);
        $originalFacadeMock->expects($this->once())
            ->method('getMediaUrl')
            ->with($exampleMediaId)
            ->willThrowException($exception = new MediaNotFoundException());

        $sut = $this->getSut(
            originalMediaFacade: $originalFacadeMock,
            fallbackMediaSettings: $settingsStub,
        );

        $this->expectExceptionObject($exception);
        $sut->getMediaUrl($exampleMediaId);
    }

    #[Test]
    public function fallbackMediaIdAlwaysRegisteredForPreload(): void
    {
        $settingsStub = $this->createConfiguredStub(FallbackMediaSettingsInterface::class, [
            'getFallbackMediaId' => $fallbackMediaId = uniqid('fallbackMediaId'),
        ]);

        $originalInput = [$originalId1 = uniqid('mediaId'), $originalId2 = uniqid('mediaId'),];
        $originalMediaFacadeSpy = $this->createMock(MediaFacadeInterface::class);
        $originalMediaFacadeSpy->expects($this->once())
            ->method('registerForPreload')
            ->with(...[$originalId1, $originalId2, $fallbackMediaId]);

        $sut = $this->getSut(
            originalMediaFacade: $originalMediaFacadeSpy,
            fallbackMediaSettings: $settingsStub,
        );
        $sut->registerForPreload(...$originalInput);
    }

    private function getSut(
        ?MediaFacadeInterface $originalMediaFacade = null,
        ?FallbackMediaSettingsInterface $fallbackMediaSettings = null,
    ): MediaFacadeInterface {
        $originalMediaFacade ??= $this->createStub(MediaFacadeInterface::class);
        $fallbackMediaSettings ??= $this->createStub(FallbackMediaSettingsInterface::class);

        return new FallbackMediaFacadeDecorator(
            originalMediaFacade: $originalMediaFacade,
            fallbackMediaSettings: $fallbackMediaSettings,
        );
    }
}
