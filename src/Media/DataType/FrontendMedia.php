<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\DataType;

class FrontendMedia
{
    public function __construct(
        public string $id,
        public string $file,
        public string $filetype,
        public int $filesize,
        public string $imageSize
    ) {
    }
}
