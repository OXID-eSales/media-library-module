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
use OxidEsales\MediaLibrary\Media\Facade\MediaFacade;
use OxidEsales\MediaLibrary\Media\Facade\MediaFacadeInterface;
use OxidEsales\MediaLibrary\Media\Repository\PreloadMediaRepositoryInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaObjectResourceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MediaFacadeTest extends TestCase
{
    #[Test]
    public function registerForPreloadTriggersRepositoryMethod(): void
    {
        $ids = [uniqid('mediaId'), uniqid('mediaId')];

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
        $id = uniqid('mediaId');

        $repositoryMock = $this->createMock(PreloadMediaRepositoryInterface::class);
        $repositoryMock->method('getMediaById')
            ->with($id)
            ->willReturn($mediaStub = $this->createStub(MediaInterface::class));

        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->never())
            ->method('warning');

        $sut = $this->getSut(
            preloadMediaRepository: $repositoryMock,
            logger: $loggerSpy,
        );

        $result = $sut->getMedia($id);

        $this->assertSame($mediaStub, $result);
    }

    #[Test]
    public function calculatesTheUrlFromRepositoryMedia(): void
    {
        $id = uniqid('mediaId');

        $repositoryMock = $this->createMock(PreloadMediaRepositoryInterface::class);
        $repositoryMock->method('getMediaById')
            ->with($id)
            ->willReturn($mediaStub = $this->createStub(MediaInterface::class));

        $mediaObjectResourceMock = $this->createMock(MediaObjectResourceInterface::class);
        $mediaObjectResourceMock->method('getUrlToMedia')
            ->with($mediaStub)
            ->willReturn($exampleUrl = uniqid('mediaUrl'));

        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->never())
            ->method('warning');

        $sut = $this->getSut(
            preloadMediaRepository: $repositoryMock,
            mediaObjectResource: $mediaObjectResourceMock,
            logger: $loggerSpy,
        );

        $result = $sut->getMediaUrl($id);

        $this->assertSame($exampleUrl, $result);
    }

    #[Test]
    public function getMediaWithoutContextLogsAWarningAndRethrowsIfMediaNotFound(): void
    {
        $id = uniqid('mediaId');

        $repositoryMock = $this->createMock(PreloadMediaRepositoryInterface::class);
        $repositoryMock->method('getMediaById')
            ->with($id)
            ->willThrowException($exception = new MediaNotFoundException());

        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->once())
            ->method('warning')
            ->with(
                'Media not found.',
                [
                    'mediaId' => $id,
                    'trigger' => '',
                    'identifier' => '',
                    'note' => '',
                ]
            );

        $sut = $this->getSut(
            preloadMediaRepository: $repositoryMock,
            logger: $loggerSpy,
        );

        $this->expectExceptionObject($exception);
        $sut->getMedia($id);
    }

    #[Test]
    public function getMediaUrlWithoutContextLogsAWarningAndRethrowsIfMediaNotFound(): void
    {
        $id = uniqid('mediaId');

        $repositoryMock = $this->createMock(PreloadMediaRepositoryInterface::class);
        $repositoryMock->method('getMediaById')
            ->with($id)
            ->willThrowException($exception = new MediaNotFoundException());

        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->once())
            ->method('warning')
            ->with(
                'Media not found.',
                [
                    'mediaId' => $id,
                    'trigger' => '',
                    'identifier' => '',
                    'note' => '',
                ]
            );

        $sut = $this->getSut(
            preloadMediaRepository: $repositoryMock,
            logger: $loggerSpy,
        );

        $this->expectExceptionObject($exception);
        $sut->getMediaUrl($id);
    }

    #[Test]
    public function lookupContextIsLoggedWithTheMediaNotFoundWarning(): void
    {
        $id = uniqid('mediaId');

        $repositoryMock = $this->createMock(PreloadMediaRepositoryInterface::class);
        $repositoryMock->method('getMediaById')
            ->with($id)
            ->willThrowException(new MediaNotFoundException());

        $contextStub = $this->createConfiguredStub(MediaLookupContextInterface::class, [
            'getTrigger' => $trigger = uniqid('trigger'),
            'getIdentifier' => $identifier = uniqid('identifier'),
            'getNote' => $note = uniqid('note'),
        ]);

        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->once())
            ->method('warning')
            ->with(
                'Media not found.',
                [
                    'mediaId' => $id,
                    'trigger' => $trigger,
                    'identifier' => $identifier,
                    'note' => $note,
                ]
            );

        $sut = $this->getSut(
            preloadMediaRepository: $repositoryMock,
            logger: $loggerSpy,
        );

        $this->expectException(MediaNotFoundException::class);
        $sut->getMediaUrl($id, $contextStub);
    }

    private function getSut(
        ?PreloadMediaRepositoryInterface $preloadMediaRepository = null,
        ?MediaObjectResourceInterface $mediaObjectResource = null,
        ?LoggerInterface $logger = null,
    ): MediaFacadeInterface {
        $preloadMediaRepository ??= $this->createStub(PreloadMediaRepositoryInterface::class);
        $mediaObjectResource ??= $this->createStub(MediaObjectResourceInterface::class);
        $logger ??= $this->createStub(LoggerInterface::class);

        return new MediaFacade(
            preloadMediaRepository: $preloadMediaRepository,
            mediaObjectResource: $mediaObjectResource,
            logger: $logger,
        );
    }
}
