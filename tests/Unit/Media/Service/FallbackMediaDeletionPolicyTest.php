<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Service;

use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Exception\MediaDeletionErrorException;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Media\Service\FallbackMediaDeletionPolicyService;
use OxidEsales\MediaLibrary\Media\Settings\FallbackMediaSettingsInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FallbackMediaDeletionPolicyService::class)]
class FallbackMediaDeletionPolicyTest extends TestCase
{
    public static function refusedDeletionDataProvider(): \Generator
    {
        $fallbackMediaId = uniqid('fallback');
        $folderId = uniqid('folder');

        yield 'the fallback media itself' => [
            'fallbackMediaId' => $fallbackMediaId,
            'fallbackFolderId' => '',
            'fallbackMediaExists' => true,
            'mediaIdsToDelete' => [$fallbackMediaId],
            'expectedMessage' => 'DD_MEDIA_REMOVE_FALLBACK_ERR:' . $fallbackMediaId,
        ];

        yield 'the folder holding the fallback media' => [
            'fallbackMediaId' => $fallbackMediaId,
            'fallbackFolderId' => $folderId,
            'fallbackMediaExists' => true,
            'mediaIdsToDelete' => [$folderId],
            'expectedMessage' => 'DD_MEDIA_REMOVE_FALLBACK_FOLDER_ERR:' . $folderId,
        ];

        yield 'the configured id even when that media does not exist' => [
            'fallbackMediaId' => $fallbackMediaId,
            'fallbackFolderId' => '',
            'fallbackMediaExists' => false,
            'mediaIdsToDelete' => [$fallbackMediaId],
            'expectedMessage' => 'DD_MEDIA_REMOVE_FALLBACK_ERR:' . $fallbackMediaId,
        ];

        yield 'a batch holding the fallback media among others' => [
            'fallbackMediaId' => $fallbackMediaId,
            'fallbackFolderId' => '',
            'fallbackMediaExists' => true,
            'mediaIdsToDelete' => [uniqid('unrelated'), $fallbackMediaId, uniqid('unrelated')],
            'expectedMessage' => 'DD_MEDIA_REMOVE_FALLBACK_ERR:' . $fallbackMediaId,
        ];

        yield 'a batch holding the folder of the fallback media among others' => [
            'fallbackMediaId' => $fallbackMediaId,
            'fallbackFolderId' => $folderId,
            'fallbackMediaExists' => true,
            'mediaIdsToDelete' => [uniqid('unrelated'), $folderId],
            'expectedMessage' => 'DD_MEDIA_REMOVE_FALLBACK_FOLDER_ERR:' . $folderId,
        ];
    }

    #[DataProvider('refusedDeletionDataProvider')]
    #[Test]
    public function refusesDeletionWithATranslatedMessageNamingTheMedia(
        string $fallbackMediaId,
        string $fallbackFolderId,
        bool $fallbackMediaExists,
        array $mediaIdsToDelete,
        string $expectedMessage
    ): void {
        $sut = $this->getSut($fallbackMediaId, $fallbackFolderId, $fallbackMediaExists);

        $this->expectException(MediaDeletionErrorException::class);
        $this->expectExceptionMessage($expectedMessage);

        $sut->validateMediaDeletion($mediaIdsToDelete);
    }

    public static function allowedDeletionDataProvider(): \Generator
    {
        yield 'unrelated media' => [
            'fallbackMediaId' => uniqid('fallback'),
            'fallbackFolderId' => uniqid('folder'),
            'fallbackMediaExists' => true,
            'mediaIdsToDelete' => [uniqid('unrelated')],
        ];

        yield 'unrelated media when the configured fallback does not exist' => [
            'fallbackMediaId' => uniqid('fallback'),
            'fallbackFolderId' => '',
            'fallbackMediaExists' => false,
            'mediaIdsToDelete' => [uniqid('unrelated')],
        ];

        yield 'the empty id when the fallback media sits in the root folder' => [
            'fallbackMediaId' => uniqid('fallback'),
            'fallbackFolderId' => '',
            'fallbackMediaExists' => true,
            'mediaIdsToDelete' => [''],
        ];

        yield 'a batch of unrelated media' => [
            'fallbackMediaId' => uniqid('fallback'),
            'fallbackFolderId' => uniqid('folder'),
            'fallbackMediaExists' => true,
            'mediaIdsToDelete' => [uniqid('unrelated'), uniqid('unrelated'), uniqid('unrelated')],
        ];

        yield 'an empty batch' => [
            'fallbackMediaId' => uniqid('fallback'),
            'fallbackFolderId' => uniqid('folder'),
            'fallbackMediaExists' => true,
            'mediaIdsToDelete' => [],
        ];
    }

    #[DataProvider('allowedDeletionDataProvider')]
    #[Test]
    public function allowsDeletion(
        string $fallbackMediaId,
        string $fallbackFolderId,
        bool $fallbackMediaExists,
        array $mediaIdsToDelete
    ): void {
        $sut = $this->getSut($fallbackMediaId, $fallbackFolderId, $fallbackMediaExists);

        $this->expectNotToPerformAssertions();

        $sut->validateMediaDeletion($mediaIdsToDelete);
    }

    #[Test]
    public function allowsEveryMediaWithoutLookingAnyUpWhenNoFallbackIsConfigured(): void
    {
        $repositorySpy = $this->createMock(MediaRepositoryInterface::class);
        $repositorySpy->expects($this->never())->method('getMediaById');

        $sut = new FallbackMediaDeletionPolicyService(
            fallbackMediaSettings: $this->createConfiguredStub(FallbackMediaSettingsInterface::class, [
                'getFallbackMediaId' => '',
            ]),
            mediaRepository: $repositorySpy,
            shopAdapter: $this->createStub(ShopAdapterInterface::class),
        );

        $sut->validateMediaDeletion([uniqid('unrelated')]);
    }

    private function getSut(
        string $fallbackMediaId = '',
        string $fallbackFolderId = '',
        bool $fallbackMediaExists = true,
    ): FallbackMediaDeletionPolicyService {
        $mediaRepositoryStub = $this->createStub(MediaRepositoryInterface::class);

        if ($fallbackMediaExists) {
            $mediaRepositoryStub->method('getMediaById')->willReturn(
                $this->createConfiguredStub(MediaInterface::class, ['getFolderId' => $fallbackFolderId])
            );
        } else {
            $mediaRepositoryStub->method('getMediaById')->willThrowException(new MediaNotFoundException());
        }

        $shopAdapterStub = $this->createStub(ShopAdapterInterface::class);
        $shopAdapterStub->method('translateString')
            ->willReturnCallback(fn(string $ident): string => $ident . ':%s');

        return new FallbackMediaDeletionPolicyService(
            fallbackMediaSettings: $this->createConfiguredStub(FallbackMediaSettingsInterface::class, [
                'getFallbackMediaId' => $fallbackMediaId,
            ]),
            mediaRepository: $mediaRepositoryStub,
            shopAdapter: $shopAdapterStub,
        );
    }
}
