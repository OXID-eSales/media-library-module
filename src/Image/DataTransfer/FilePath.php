<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\DataTransfer;

class FilePath implements FilePathInterface
{
    public function __construct(
        readonly private string $filePath
    ) {
    }

    public function getPath()
    {
        return $this->filePath;
    }

    public function getFileName()
    {
        return basename($this->filePath);
    }
}
