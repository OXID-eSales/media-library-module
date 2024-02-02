<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Image\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use Symfony\Component\Filesystem\Path;

class ThumbnailResource implements ThumbnailResourceInterface
{
    public const THUMBNAIL_DEFAULT_SIZE = 185;
    public const THUMBNAIL_DIRECTORY = 'thumbs';

    public function __construct(
        protected ImageResourceInterface $oldImageResource,
        protected ImageResourceRefactoredInterface $imageResource,
    ) {
    }

    public function calculateMediaThumbnailUrl(string $fileName, string $fileType): string
    {
        $result = '';

        if ($fileType !== 'directory') {
            $result = $this->oldImageResource->getThumbnailUrl($fileName);
        }

        return $result;
    }

    public function getThumbnailFileName(
        string $originalFileName,
        ImageSizeInterface $thumbnailSize,
        bool $crop
    ): string {
        return sprintf(
            '%s_thumb_%d*%d%s%s',
            md5($originalFileName),
            $thumbnailSize->getWidth(),
            $thumbnailSize->getHeight(),
            $crop ? '' : '_nocrop',
            '.jpg'
        );
    }

    public function getDefaultThumbnailSize(): ImageSizeInterface
    {
        return new ImageSize(width: self::THUMBNAIL_DEFAULT_SIZE, height: self::THUMBNAIL_DEFAULT_SIZE);
    }

    public function getPathToThumbnailFiles(string $folder = ''): string
    {
        return Path::join(
            $this->imageResource->getPathToMediaFiles($folder),
            self::THUMBNAIL_DIRECTORY
        );
    }

    public function getUrlToThumbnailFiles(string $folder = ''): string
    {
        return Path::join(
            $this->imageResource->getUrlToMedia(folder: $folder),
            self::THUMBNAIL_DIRECTORY
        );
    }
}
