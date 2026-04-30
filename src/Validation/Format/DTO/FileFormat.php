<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Format\DTO;

final class FileFormat implements FileFormatInterface
{
    private readonly string $extension;

    /**
     * @param string[] $mimeTypes
     */
    public function __construct(
        string $extension,
        private readonly array $mimeTypes,
    ) {
        $this->extension = strtolower($extension);
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
