<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Facade;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\Repository\PreloadMediaRepositoryInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaObjectResourceInterface;

class MediaFacade implements MediaFacadeInterface
{
    public function __construct(
        private readonly PreloadMediaRepositoryInterface $preloadMediaRepository,
        private readonly MediaObjectResourceInterface $mediaObjectResource,
    ) {
    }

    public function registerForPreload(string ...$mediaIds): void
    {
        $this->preloadMediaRepository->registerForPreload(...$mediaIds);
    }

    public function getMedia(string $mediaId): MediaInterface
    {
        return $this->getMediaObject($mediaId);
    }

    public function getMediaUrl(string $mediaId): string
    {
        return $this->mediaObjectResource->getUrlToMedia($this->getMediaObject($mediaId));
    }

    /**
     * @throws MediaNotFoundException
     */
    public function getMediaObject(string $mediaId): MediaInterface
    {
        return $this->preloadMediaRepository->getMediaById($mediaId);
    }
}
