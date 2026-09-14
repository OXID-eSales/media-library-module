<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Facade;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\DataType\MediaLookupContext;
use OxidEsales\MediaLibrary\Media\DataType\MediaLookupContextInterface;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\Settings\FallbackMediaSettingsInterface;

class FallbackMediaFacadeDecorator implements MediaFacadeInterface
{
    public function __construct(
        private readonly MediaFacadeInterface $originalMediaFacade,
        private readonly FallbackMediaSettingsInterface $fallbackMediaSettings,
    ) {
    }

    public function registerForPreload(string ...$mediaIds): void
    {
        $fallBackMediaId = $this->fallbackMediaSettings->getFallbackMediaId();
        array_push($mediaIds, $fallBackMediaId);

        $this->originalMediaFacade->registerForPreload(...$mediaIds);
    }

    public function getMedia(string $mediaId, ?MediaLookupContextInterface $context = null): MediaInterface
    {
        try {
            $result = $this->originalMediaFacade->getMedia($mediaId, $context);
        } catch (MediaNotFoundException $exception) {
            $fallbackMediaId = $this->fallbackMediaSettings->getFallbackMediaId();
            if ($fallbackMediaId === '') {
                throw $exception;
            }

            $result = $this->originalMediaFacade->getMedia(
                $fallbackMediaId,
                $this->createFallbackLookupContext($mediaId)
            );
        }

        return $result;
    }

    public function getMediaUrl(string $mediaId, ?MediaLookupContextInterface $context = null): string
    {
        try {
            $result = $this->originalMediaFacade->getMediaUrl($mediaId, $context);
        } catch (MediaNotFoundException $exception) {
            $fallbackMediaId = $this->fallbackMediaSettings->getFallbackMediaId();
            if ($fallbackMediaId === '') {
                throw $exception;
            }

            $result = $this->originalMediaFacade->getMediaUrl(
                $fallbackMediaId,
                $this->createFallbackLookupContext($mediaId)
            );
        }

        return $result;
    }

    private function createFallbackLookupContext(string $originalMediaId): MediaLookupContextInterface
    {
        return new MediaLookupContext(
            trigger: 'MediaLibrary/FallbackMedia',
            identifier: $originalMediaId,
        );
    }
}
