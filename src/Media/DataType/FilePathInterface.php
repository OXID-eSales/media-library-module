<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Media\DataType;

interface FilePathInterface
{
    public function getPath(): string;

    public function getFileName(): string;

    public function getExtension(): string;
}
