<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\FilePathInterface;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;

interface MediaResourceInterface
{
    public function getPathToMediaFiles(string $folderName = ''): string;

    public function getPathToMediaFile(string $folderName = '', string $fileName = ''): string;

    public function getPathToMedia(MediaInterface $media): string;

    public function getUrlToMediaFiles(string $folderName = ''): string;

    public function getUrlToMediaFile(string $folderName = '', string $fileName = ''): string;

    public function getPossibleMediaFilePath(string $folderName = '', string $fileName = ''): FilePathInterface;
}
