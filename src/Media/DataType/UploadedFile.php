<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\DataType;

class UploadedFile implements UploadedFileInterface
{
    public function __construct(
        private array $fileData
    ) {
    }

    public function getFileName(): string
    {
        return $this->fileData['name'] ?? '';
    }

    public function getFilePath(): string
    {
        return $this->fileData['tmp_name'] ?? '';
    }

    public function isError(): bool
    {
        return !isset($this->fileData['error']) || $this->fileData['error'] !== UPLOAD_ERR_OK;
    }

    public function getSize(): int
    {
        return $this->fileData['size'] ?? 0;
    }

    public function getFileType(): string
    {
        return $this->fileData['type'] ?? '';
    }
}
