<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Service;

use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\MediaLibrary\Media\Exception\MediaDeletionErrorException;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Media\Settings\FallbackMediaSettingsInterface;

class FallbackMediaDeletionPolicyService implements MediaDeletionPolicyServiceInterface
{
    private const IDENT_FALLBACK_MEDIA = 'DD_MEDIA_REMOVE_FALLBACK_ERR';
    private const IDENT_FALLBACK_MEDIA_FOLDER = 'DD_MEDIA_REMOVE_FALLBACK_FOLDER_ERR';

    public function __construct(
        private readonly FallbackMediaSettingsInterface $fallbackMediaSettings,
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly ShopAdapterInterface $shopAdapter,
    ) {
    }

    public function validateMediaDeletion(array $ids): void
    {
        $fallbackMediaId = $this->fallbackMediaSettings->getFallbackMediaId();

        if ($fallbackMediaId === '') {
            return;
        }

        $fallbackFolderId = $this->getFallbackMediaFolderId($fallbackMediaId);

        foreach ($ids as $mediaId) {
            if ($mediaId === $fallbackMediaId) {
                throw new MediaDeletionErrorException(
                    sprintf($this->shopAdapter->translateString(self::IDENT_FALLBACK_MEDIA), $mediaId)
                );
            }

            if ($fallbackFolderId !== '' && $mediaId === $fallbackFolderId) {
                throw new MediaDeletionErrorException(
                    sprintf($this->shopAdapter->translateString(self::IDENT_FALLBACK_MEDIA_FOLDER), $mediaId)
                );
            }
        }
    }

    private function getFallbackMediaFolderId(string $fallbackMediaId): string
    {
        try {
            return $this->mediaRepository->getMediaById($fallbackMediaId)->getFolderId();
        } catch (MediaNotFoundException) {
            return '';
        }
    }
}
