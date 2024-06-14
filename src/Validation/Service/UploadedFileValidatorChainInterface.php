<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Validation\Service;

use OxidEsales\MediaLibrary\Media\DataType\UploadedFileInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;

interface UploadedFileValidatorChainInterface
{
    /**
     * @throws ValidationFailedException
     */
    public function validateFile(UploadedFileInterface $uploadedFile): void;
}
