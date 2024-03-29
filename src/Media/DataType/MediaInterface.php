<?php

namespace OxidEsales\MediaLibrary\Media\DataType;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;

interface MediaInterface
{
    public function getFileType(): string;

    public function getImageSize(): ImageSizeInterface;

    public function getFolderId(): string;

    public function getFolderName(): string;

    public function getFileSize(): int;

    public function getFileName(): string;

    public function getOxid(): string;

    public function isDirectory(): bool;
}
