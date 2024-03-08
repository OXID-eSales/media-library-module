<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Image\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaResourceInterface;
use Symfony\Component\Filesystem\Path;

class ThumbnailResource implements ThumbnailResourceInterface
{
    public const THUMBNAIL_DEFAULT_SIZE = 185;
    public const THUMBNAIL_DIRECTORY = 'thumbs';

    public function __construct(
        protected MediaResourceInterface $mediaResource,
    ) {
    }

    public function getDefaultThumbnailSize(): ImageSizeInterface
    {
        return new ImageSize(width: self::THUMBNAIL_DEFAULT_SIZE, height: self::THUMBNAIL_DEFAULT_SIZE);
    }

    public function getPathToThumbnailFiles(string $folderName = ''): string
    {
        return Path::join(
            $this->mediaResource->getPathToMediaFiles($folderName),
            self::THUMBNAIL_DIRECTORY
        );
    }

    public function getUrlToThumbnailFiles(string $folderName = ''): string
    {
        return Path::join(
            $this->mediaResource->getUrlToMediaFiles(folderName: $folderName),
            self::THUMBNAIL_DIRECTORY
        );
    }
}
