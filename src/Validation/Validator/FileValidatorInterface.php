<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Validation\Validator;

use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;

interface FileValidatorInterface
{
    /**
     * @throws ValidationFailedException
     */
    public function validateFile(string $filePath): void;
}
