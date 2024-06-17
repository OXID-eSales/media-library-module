<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Media\DataType;

interface UploadedFileInterface extends FilePathInterface
{
    public function getFileType(): string;

    public function isError(): bool;

    public function getSize(): int;
}
