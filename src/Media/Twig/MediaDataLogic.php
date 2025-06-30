<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Twig;

use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\Repository\PreloadMediaRepositoryInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaObjectResourceInterface;

class MediaDataLogic implements MediaDataLogicInterface
{
    public function __construct(
        private readonly PreloadMediaRepositoryInterface $mediaRepository,
        private readonly MediaObjectResourceInterface $mediaObjectResource
    ) {
    }

    public function getMediaUrl(string $mediaId): string
    {
        try {
            $media = $this->mediaRepository->getMediaById($mediaId);
            $url = $this->mediaObjectResource->getUrlToMedia($media);
        } catch (MediaNotFoundException $e) {
            // todo: log this case
            $url = '';
        }

        return $url;
    }
}
