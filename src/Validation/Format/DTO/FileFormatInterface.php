<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Validation\Format\DTO;

interface FileFormatInterface
{
    public function getExtension(): string;

    /**
     * @return string[]
     */
    public function getMimeTypes(): array;
}
