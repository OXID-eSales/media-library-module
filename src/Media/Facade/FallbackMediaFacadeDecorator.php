<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Facade;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\Settings\FallbackMediaSettingsInterface;
use Psr\Log\LoggerInterface;

class FallbackMediaFacadeDecorator implements MediaFacadeInterface
{
    public function __construct(
        private readonly MediaFacadeInterface $originalMediaFacade,
        private readonly FallbackMediaSettingsInterface $fallbackMediaSettings,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function registerForPreload(string ...$mediaIds): void
    {
        $fallBackMediaId = $this->fallbackMediaSettings->getFallbackMediaId();
        array_push($mediaIds, $fallBackMediaId);

        $this->originalMediaFacade->registerForPreload(...$mediaIds);
    }

    public function getMedia(string $mediaId): MediaInterface
    {
        try {
            $result = $this->originalMediaFacade->getMedia($mediaId);
        } catch (MediaNotFoundException $exception) {
            $fallbackMediaId = $this->fallbackMediaSettings->getFallbackMediaId();
            if ($fallbackMediaId === '') {
                throw $exception;
            }

            $this->logWarning($mediaId);

            $result = $this->originalMediaFacade->getMedia($fallbackMediaId);
        }

        return $result;
    }

    public function getMediaUrl(string $mediaId): string
    {
        try {
            $result = $this->originalMediaFacade->getMediaUrl($mediaId);
        } catch (MediaNotFoundException $exception) {
            $fallbackMediaId = $this->fallbackMediaSettings->getFallbackMediaId();
            if ($fallbackMediaId === '') {
                throw $exception;
            }

            $this->logWarning($mediaId);

            $result = $this->originalMediaFacade->getMediaUrl($fallbackMediaId);
        }

        return $result;
    }

    private function logWarning(string $mediaId): void
    {
        $this->logger->warning(
            'Media not found, using fallback media.',
            ['mediaId' => $mediaId]
        );
    }
}
