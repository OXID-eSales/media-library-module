<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Facade;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\DataType\MediaLookupContextInterface;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\Repository\PreloadMediaRepositoryInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaObjectResourceInterface;
use Psr\Log\LoggerInterface;

class MediaFacade implements MediaFacadeInterface
{
    public function __construct(
        private readonly PreloadMediaRepositoryInterface $preloadMediaRepository,
        private readonly MediaObjectResourceInterface $mediaObjectResource,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function registerForPreload(string ...$mediaIds): void
    {
        $this->preloadMediaRepository->registerForPreload(...$mediaIds);
    }

    public function getMedia(string $mediaId, ?MediaLookupContextInterface $context = null): MediaInterface
    {
        return $this->getMediaObject($mediaId, $context);
    }

    public function getMediaUrl(string $mediaId, ?MediaLookupContextInterface $context = null): string
    {
        $mediaObject = $this->getMediaObject($mediaId, $context);
        return $this->mediaObjectResource->getUrlToMedia($mediaObject);
    }

    /**
     * @throws MediaNotFoundException
     */
    private function getMediaObject(string $mediaId, ?MediaLookupContextInterface $context): MediaInterface
    {
        try {
            return $this->preloadMediaRepository->getMediaById($mediaId);
        } catch (MediaNotFoundException $exception) {
            $this->logMediaNotFound($mediaId, $context);
            throw $exception;
        }
    }

    private function logMediaNotFound(string $mediaId, ?MediaLookupContextInterface $context): void
    {
        $this->logger->warning(
            'Media not found.',
            [
                'mediaId' => $mediaId,
                'trigger' => $context?->getTrigger() ?? '',
                'identifier' => $context?->getIdentifier() ?? '',
                'note' => $context?->getNote() ?? '',
            ]
        );
    }
}
