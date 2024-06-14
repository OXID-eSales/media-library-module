<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Media\DataType;

interface UploadedFileInterface
{
    public function getFileName(): string;

    public function getFileType(): string;

    public function getFilePath(): string;

    public function isError(): bool;

    public function getSize(): int;
}
