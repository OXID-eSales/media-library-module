<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Twig;

use OxidEsales\MediaLibrary\Media\DataType\MediaLookupContext;
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
            return $this->mediaFacade->getMediaUrl(
                $mediaId,
                new MediaLookupContext(
                    trigger: 'MediaLibrary/TwigFunction',
                    identifier: 'oeMediaUrl',
                )
            );
        } catch (MediaNotFoundException) {
            return '';
        }
    }

    public function getMediaAltText(string $objectId): string
    {
        try {
            $media = $this->mediaFacade->getMedia(
                $objectId,
                new MediaLookupContext(
                    trigger: 'MediaLibrary/TwigFunction',
                    identifier: 'oeMediaAlt',
                )
            );
            return $media->getMediaAltText();
        } catch (MediaNotFoundException) {
            return '';
        }
    }
}
