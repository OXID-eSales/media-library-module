<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Validation\Validator;

use OxidEsales\MediaLibrary\Media\DataType\UploadedFileInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;

interface UploadedFileValidatorInterface
{
    /**
     * @throws ValidationFailedException
     */
    public function validateFile(UploadedFileInterface $uploadedFile): void;
}
