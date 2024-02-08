<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\DataTransfer;

interface FilePathInterface
{
    public function getPath();

    public function getFileName();
}
