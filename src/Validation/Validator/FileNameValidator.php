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
    public function validateFile(FilePathInterface $filePath): void
    {
        $fileName = $filePath->getFileName();

        $this->checkFilenameNotEmpty($fileName);
        $this->checkFilenameDoesNotStartWithDot($fileName);
    }

    public function checkFilenameNotEmpty(string $fileName): void
    {
        if (!$fileName) {
            throw new ValidationFailedException("OE_MEDIA_LIBRARY_EXCEPTION_FILENAME_EMPTY");
        }
    }

    public function checkFilenameDoesNotStartWithDot(string $fileName): void
    {
        if ($fileName[0] === '.') {
            throw new ValidationFailedException("OE_MEDIA_LIBRARY_EXCEPTION_FILENAME_STARTS_DOT");
        }
    }
}
