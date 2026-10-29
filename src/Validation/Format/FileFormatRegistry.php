<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Format;

use OxidEsales\MediaLibrary\Validation\Format\DTO\FileFormat;
use OxidEsales\MediaLibrary\Validation\Format\DTO\FileFormatInterface;

final class FileFormatRegistry implements FileFormatRegistryInterface
{
    /** @var array<lowercase-string, list<string>> */
    private const MIME_TYPES_BY_EXTENSION = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'gif' => ['image/gif'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        'avif' => ['image/avif', 'image/avif-sequence'],
        'svg' => ['image/svg+xml', 'image/svg', 'text/xml', 'application/xml'],
        'pdf' => ['application/pdf'],
        'mp3' => ['audio/mpeg'],
        'avi' => ['video/x-msvideo', 'video/avi'],
        'mpg' => ['video/mpeg'],
        'mpeg' => ['video/mpeg'],
        'doc' => ['application/msword', 'application/vnd.ms-office', 'application/CDFV2'],
        'xls' => ['application/vnd.ms-excel', 'application/vnd.ms-office', 'application/CDFV2'],
        'ppt' => ['application/vnd.ms-powerpoint', 'application/vnd.ms-office', 'application/CDFV2'],
        'zip' => ['application/zip', 'application/x-zip-compressed'],
    ];

    /** @var array<string, FileFormatInterface> */
    private array $formatsByExtension = [];

    public function __construct()
    {
        foreach (self::MIME_TYPES_BY_EXTENSION as $extension => $mimeTypes) {
            $this->formatsByExtension[$extension] = new FileFormat(
                extension: $extension,
                mimeTypes: $mimeTypes,
            );
        }
    }

    public function findByExtension(string $extension): ?FileFormatInterface
    {
        return $this->formatsByExtension[strtolower($extension)] ?? null;
    }
}
