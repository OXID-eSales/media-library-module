<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Validation\Service;

use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;

interface FileValidatorChainInterface
{
    /**
     * @throws ValidationFailedException
     */
    public function validateFile(string $filePath): void;
}
