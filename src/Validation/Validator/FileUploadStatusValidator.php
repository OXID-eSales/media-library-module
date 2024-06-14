<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Validator;

use OxidEsales\MediaLibrary\Media\DataType\UploadedFileInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;

class FileUploadStatusValidator implements UploadedFileValidatorInterface
{
    public function validateFile(UploadedFileInterface $uploadedFile): void
    {
        if (!file_exists($uploadedFile->getFilePath())) {
            throw new ValidationFailedException('File was not uploaded');
        }
    }
}
