<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\DataType;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;

class Media implements MediaInterface
{
    public const FILETYPE_DIRECTORY = 'directory';

    public function __construct(
        private string $oxid,
        private string $fileName,
        private int $fileSize,
        private string $fileType,
        private string $thumbFileName,
        private ImageSizeInterface $imageSize,
        private string $folderId
    ) {
    }

    public function getFileType(): string
    {
        return $this->fileType;
    }

    public function getThumbFileName(): string
    {
        return $this->thumbFileName;
    }

    public function getImageSize(): ImageSizeInterface
    {
        return $this->imageSize;
    }

    public function getFolderId(): string
    {
        return $this->folderId;
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

    public function getFrontendData(): FrontendMedia
    {
        $size = $this->getImageSize();
        return new FrontendMedia(
            id: $this->getOxid(),
            file: $this->getFileName(),
            filetype: $this->getFileType(),
            filesize: $this->getFileSize(),
            thumb: $this->getThumbFileName(),
            imageSize: $size->getInFormat("%dx%d", '')
        );
    }
}
