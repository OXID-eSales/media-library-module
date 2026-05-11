<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Format\DTO;

final class FileFormat implements FileFormatInterface
{
    /**
     * @param string[] $mimeTypes
     */
    public function __construct(
        private readonly string $extension,
        private readonly array $mimeTypes,
    ) {
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @return string[]
     */
    public function getMimeTypes(): array
    {
        return $this->mimeTypes;
    }
}
