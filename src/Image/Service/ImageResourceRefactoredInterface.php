<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\FilePathInterface;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;

interface ImageResourceRefactoredInterface
{
    public function getPathToMediaFiles(string $folder = ''): string;

    public function getPathToMediaFile(MediaInterface $media): string;

    public function getUrlToMedia(string $folder = '', string $fileName = ''): string;

    public function getPossibleMediaFilePath(string $folderName = '', string $fileName = ''): FilePathInterface;
}
