<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Image\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;

interface ImageResourceInterface
{
    public function getFolderName(): string;

    public function setFolderName($sFolderName): void;

    public function getFolderId(): string;

    public function setFolder($sFolderId = ''): void;

    public function getDefaultThumbnailSize(): int;

    public function getThumbName($sFile, ?ImageSizeInterface $imageSize = null, $thumbnailCrop = true): string;

    public function getThumbnailUrl($sFile = '', ?ImageSizeInterface $imageSize = null, $thumbnailCrop = true): string;

    public function getThumbnailPath(string $filename = ''): string;

    public function getMediaUrl($filename = '');

    public function getMediaPath($filename = '', $blDoNotSetFolder = false): string;

    public function createThumbnail($sFileName, ?ImageSizeInterface $imageSize = null, $thumbnailCrop = true);
}
