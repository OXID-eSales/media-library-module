<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\DataType;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;

class Media implements MediaInterface
{
    public const FILETYPE_DIRECTORY = 'directory';

    public function __construct(
        private string $oxid,
        private string $fileName,
        private int $fileSize = 0,
        private string $fileType = '',
        private ImageSizeInterface $imageSize = new ImageSize(0, 0),
        private string $folderId = '',
        private string $folderName = ''
    ) {
    }

    public function getFileType(): string
    {
        return $this->fileType;
    }

    public function getImageSize(): ImageSizeInterface
    {
        return $this->imageSize;
    }

    public function getFolderId(): string
    {
        return $this->folderId;
    }

    public function getFolderName(): string
    {
        return $this->folderName;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getOxid(): string
    {
        return $this->oxid;
    }

    public function isDirectory(): bool
    {
        return $this->getFileType() == self::FILETYPE_DIRECTORY;
    }
}
