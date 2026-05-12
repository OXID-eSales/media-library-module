<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Format\DTO;

interface FileFormatInterface
{
    public function getExtension(): string;

    /**
     * @return string[]
     */
    public function getMimeTypes(): array;
}
