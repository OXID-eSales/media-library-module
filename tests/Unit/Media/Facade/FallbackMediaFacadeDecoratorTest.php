<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Facade;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\Facade\FallbackMediaFacadeDecorator;
use OxidEsales\MediaLibrary\Media\Facade\MediaFacadeInterface;
use OxidEsales\MediaLibrary\Media\Settings\FallbackMediaSettingsInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FallbackMediaFacadeDecoratorTest extends TestCase
{
    #[Test]
    public function getMediaReturnsOriginalResultIfItsFine(): void
    {
        $originalFacadeMock = $this->createMock(MediaFacadeInterface::class);
        $exampleMediaId = uniqid();
        $originalFacadeMock->method('getMedia')
            ->with($exampleMediaId)
            ->willReturn($mediaStub = $this->createStub(MediaInterface::class));

        $sut = $this->getSut(
            originalMediaFacade: $originalFacadeMock,
        );

        $result = $sut->getMedia($exampleMediaId);
        $this->assertSame($mediaStub, $result);
    }

    #[Test]
    public function getMediaUrlReturnsOriginalResultIfItsFine()
    {
        $originalFacadeMock = $this->createMock(MediaFacadeInterface::class);
        $exampleMediaId = uniqid();
        $originalFacadeMock->method('getMediaUrl')
            ->with($exampleMediaId)
            ->willReturn($originalMediaUrl = uniqid());

        $sut = $this->getSut(
            originalMediaFacade: $originalFacadeMock,
        );

        $result = $sut->getMediaUrl($exampleMediaId);
        $this->assertSame($originalMediaUrl, $result);
    }

    #[Test]
    public function getMediaReturnsFallbackResultAndLogsIfOriginalMediaNotFound(): void
    {
        $exampleMediaId = uniqid();
        $settingsStub = $this->createConfiguredStub(FallbackMediaSettingsInterface::class, [
            'getFallbackMediaId' => $fallbackMediaId = uniqid(),
        ]);

        $fallbackMediaStub = $this->createStub(MediaInterface::class);
        $originalFacadeMock = $this->createMock(MediaFacadeInterface::class);
        $originalFacadeMock->method('getMedia')
            ->willReturnCallback(function (string $mediaId) use (
                $fallbackMediaId,
                $fallbackMediaStub,
            ) {
                if ($mediaId === $fallbackMediaId) {
                    return $fallbackMediaStub;
                }
                throw new MediaNotFoundException();
            });

        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->once())
            ->method('warning')
            ->with(
                'Media not found, using fallback media.',
                ['mediaId' => $exampleMediaId]
            );

        $sut = $this->getSut(
            originalMediaFacade: $originalFacadeMock,
            fallbackMediaSettings: $settingsStub,
            logger: $loggerSpy,
        );

        $result = $sut->getMedia($exampleMediaId);
        $this->assertSame($fallbackMediaStub, $result);
    }

    #[Test]
    public function getMediaUrlReturnsFallbackResultAndLogsIfOriginalMediaNotFound(): void
    {
        $exampleMediaId = uniqid();
        $settingsStub = $this->createConfiguredStub(FallbackMediaSettingsInterface::class, [
            'getFallbackMediaId' => $fallbackMediaId = uniqid(),
        ]);

        $fallbackMediaUrl = uniqid();
        $originalFacadeMock = $this->createMock(MediaFacadeInterface::class);
        $originalFacadeMock->method('getMediaUrl')
            ->willReturnCallback(function (string $mediaId) use (
                $fallbackMediaId,
                $fallbackMediaUrl,
            ) {
                if ($mediaId === $fallbackMediaId) {
                    return $fallbackMediaUrl;
                }
                throw new MediaNotFoundException();
            });

        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->once())
            ->method('warning')
            ->with(
                'Media not found, using fallback media.',
                ['mediaId' => $exampleMediaId]
            );

        $sut = $this->getSut(
            originalMediaFacade: $originalFacadeMock,
            fallbackMediaSettings: $settingsStub,
            logger: $loggerSpy,
        );

        $result = $sut->getMediaUrl($exampleMediaId);
        $this->assertSame($fallbackMediaUrl, $result);
    }

    #[Test]
    public function getMediaThrowsExceptionIfOriginalAndFallbackMediaNotFound(): void
    {
        $exampleMediaId = uniqid();
        $settingsStub = $this->createConfiguredStub(FallbackMediaSettingsInterface::class, [
            'getFallbackMediaId' => uniqid(),
        ]);

        $originalFacadeMock = $this->createMock(MediaFacadeInterface::class);
        $originalFacadeMock->method('getMedia')
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
        $exampleMediaId = uniqid();
        $settingsStub = $this->createConfiguredStub(FallbackMediaSettingsInterface::class, [
            'getFallbackMediaId' => uniqid(),
        ]);

        $originalFacadeMock = $this->createMock(MediaFacadeInterface::class);
        $originalFacadeMock->method('getMediaUrl')
            ->willThrowException(new MediaNotFoundException());

        $sut = $this->getSut(
            originalMediaFacade: $originalFacadeMock,
            fallbackMediaSettings: $settingsStub,
        );

        $this->expectException(MediaNotFoundException::class);
        $sut->getMediaUrl($exampleMediaId);
    }

    #[Test]
    public function getMediaKeepsTheRequestedIdInTheExceptionIfNoFallbackConfigured(): void
    {
        $exampleMediaId = uniqid();
        $settingsStub = $this->createConfiguredStub(FallbackMediaSettingsInterface::class, [
            'getFallbackMediaId' => '',
        ]);

        $originalFacadeMock = $this->createMock(MediaFacadeInterface::class);
        $originalFacadeMock->expects($this->once())
            ->method('getMedia')
            ->with($exampleMediaId)
            ->willThrowException(
                new MediaNotFoundException(sprintf('Media with id "%s" not found.', $exampleMediaId))
            );

        $sut = $this->getSut(
            originalMediaFacade: $originalFacadeMock,
            fallbackMediaSettings: $settingsStub,
        );

        $this->expectException(MediaNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Media with id "%s" not found.', $exampleMediaId));
        $sut->getMedia($exampleMediaId);
    }

    #[Test]
    public function getMediaUrlKeepsTheRequestedIdInTheExceptionIfNoFallbackConfigured(): void
    {
        $exampleMediaId = uniqid();
        $settingsStub = $this->createConfiguredStub(FallbackMediaSettingsInterface::class, [
            'getFallbackMediaId' => '',
        ]);

        $originalFacadeMock = $this->createMock(MediaFacadeInterface::class);
        $originalFacadeMock->expects($this->once())
            ->method('getMediaUrl')
            ->with($exampleMediaId)
            ->willThrowException(
                new MediaNotFoundException(sprintf('Media with id "%s" not found.', $exampleMediaId))
            );

        $sut = $this->getSut(
            originalMediaFacade: $originalFacadeMock,
            fallbackMediaSettings: $settingsStub,
        );

        $this->expectException(MediaNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Media with id "%s" not found.', $exampleMediaId));
        $sut->getMediaUrl($exampleMediaId);
    }

    #[Test]
    public function fallbackMediaIdAlwaysRegisteredForPreload(): void
    {
        $settingsStub = $this->createConfiguredStub(FallbackMediaSettingsInterface::class, [
            'getFallbackMediaId' => $fallbackMediaId = uniqid(),
        ]);

        $originalInput = [$originalId1 = uniqid(), $originalId2 = uniqid(),];
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
        ?LoggerInterface $logger = null,
    ): MediaFacadeInterface {
        $originalMediaFacade ??= $this->createStub(MediaFacadeInterface::class);
        $fallbackMediaSettings ??= $this->createStub(FallbackMediaSettingsInterface::class);
        $logger ??= $this->createStub(LoggerInterface::class);

        return new FallbackMediaFacadeDecorator(
            originalMediaFacade: $originalMediaFacade,
            fallbackMediaSettings: $fallbackMediaSettings,
            logger: $logger,
        );
    }
}
