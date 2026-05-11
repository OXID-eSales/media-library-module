<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\DataType;

class FilePath implements FilePathInterface
{
    public function __construct(
        readonly private string $filePath
    ) {
    }

    public function getPath(): string
    {
        return $this->filePath;
    }

    public function getFileName(): string
    {
        return basename($this->filePath);
    }

    public function getExtension(): string
    {
        return pathinfo($this->getFileName(), PATHINFO_EXTENSION);
    }
}
