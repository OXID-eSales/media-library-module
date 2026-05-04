<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Validator;

use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;

class FileNameValidator implements FilePathValidatorInterface
{
    private const INVALID_PATH_MESSAGE = "OE_MEDIA_LIBRARY_EXCEPTION_FILENAME_INVALID_PATH";

    public function validateFile(FilePathInterface $filePath): void
    {
        $fileName = $filePath->getFileName();

        $this->checkFilenameNotEmpty($fileName);
        $this->checkFilenameDoesNotStartWithDot($fileName);
        $this->checkFilenameHasNoPathSeparator($fileName);
        $this->checkFilenameHasNoTraversalSegment($fileName);
        $this->checkFilenameHasNoNullByte($fileName);
    }

    private function checkFilenameNotEmpty(string $fileName): void
    {
        if (!$fileName) {
            throw new ValidationFailedException("OE_MEDIA_LIBRARY_EXCEPTION_FILENAME_EMPTY");
        }
    }

    private function checkFilenameDoesNotStartWithDot(string $fileName): void
    {
        if ($fileName[0] === '.') {
            throw new ValidationFailedException("OE_MEDIA_LIBRARY_EXCEPTION_FILENAME_STARTS_DOT");
        }
    }

    private function checkFilenameHasNoPathSeparator(string $fileName): void
    {
        if (str_contains($fileName, '/') || str_contains($fileName, '\\')) {
            throw new ValidationFailedException(self::INVALID_PATH_MESSAGE);
        }
    }

    private function checkFilenameHasNoTraversalSegment(string $fileName): void
    {
        if (str_contains($fileName, '..')) {
            throw new ValidationFailedException(self::INVALID_PATH_MESSAGE);
        }
    }

    private function checkFilenameHasNoNullByte(string $fileName): void
    {
        if (str_contains($fileName, "\0")) {
            throw new ValidationFailedException(self::INVALID_PATH_MESSAGE);
        }
    }
}
