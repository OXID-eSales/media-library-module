<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Validator;

use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Service\FileSystemServiceInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use OxidEsales\MediaLibrary\Validation\Format\FileFormatRegistryInterface;

final class MimeTypeValidator implements FilePathValidatorInterface
{
    private const INVALID_FILE_MIME_MESSAGE = 'OE_MEDIA_LIBRARY_EXCEPTION_INVALID_FILE_MIME';

    public function __construct(
        private readonly FileFormatRegistryInterface $registry,
        private readonly FileSystemServiceInterface $fileSystemService,
    ) {
    }

    public function validateFile(FilePathInterface $filePath): void
    {
        $format = $this->registry->findByExtension($filePath->getExtension());
        if ($format === null) {
            return;
        }

        $sniffedMimeType = $this->fileSystemService->getMimeType($filePath->getPath());
        if (!in_array($sniffedMimeType, $format->getMimeTypes(), true)) {
            throw new ValidationFailedException(self::INVALID_FILE_MIME_MESSAGE);
        }
    }
}
