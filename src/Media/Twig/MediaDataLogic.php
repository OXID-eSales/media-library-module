<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Twig;

use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\Facade\MediaFacadeInterface;

class MediaDataLogic implements MediaDataLogicInterface
{
    public function __construct(
        private readonly MediaFacadeInterface $mediaFacade,
    ) {
    }

    public function getMediaUrl(string $mediaId): string
    {
        try {
            $url = $this->mediaFacade->getMediaUrl($mediaId);
        } catch (MediaNotFoundException $e) {
            // todo: log this case
            $url = '';
        }

        return $url;
    }

    public function getMediaAltText(string $objectId): string
    {
        try {
            $media = $this->mediaFacade->getMedia($objectId);
            return $media->getMediaAltText();
        } catch (MediaNotFoundException $e) {
            // TODO: log exception
        }
        return '';
    }
}
