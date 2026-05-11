<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Format;

use OxidEsales\MediaLibrary\Validation\Format\DTO\FileFormatInterface;

final class FileFormatRegistry implements FileFormatRegistryInterface
{
    /** @var array<string, FileFormatInterface> */
    private array $formatsByExtension = [];

    /**
     * @param iterable<FileFormatInterface> $formats
     */
    public function __construct(iterable $formats)
    {
        foreach ($formats as $format) {
            $this->formatsByExtension[strtolower($format->getExtension())] = $format;
        }
    }

    public function findByExtension(string $extension): ?FileFormatInterface
    {
        return $this->formatsByExtension[strtolower($extension)] ?? null;
    }
}
