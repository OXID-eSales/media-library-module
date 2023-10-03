<?php

namespace OxidEsales\MediaLibrary\Media\DataType;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;

interface MediaInterface
{
    public function getFileType(): string;

    public function getThumbFileName(): string;

    public function getImageSize(): ImageSizeInterface;

    public function getFolderId(): string;

    public function getFileSize(): int;

    public function getFileName(): string;

    public function getShopId(): int;

    public function getOxid(): string;
}