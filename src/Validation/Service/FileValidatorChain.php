<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Service;

use OxidEsales\MediaLibrary\Validation\Exception\ChainInputTypeException;
use OxidEsales\MediaLibrary\Validation\Validator\FileValidatorInterface;

class FileValidatorChain implements FileValidatorChainInterface
{
    /**
     * @param iterable<FileValidatorInterface> $fileValidators
     */
    public function __construct(
        private iterable $fileValidators
    ) {
        foreach ($this->fileValidators as $oneValidator) {
            if (!$oneValidator instanceof FileValidatorInterface) {
                throw new ChainInputTypeException();
            }
        }
    }

    public function validateFile(string $filePath): void
    {
        foreach ($this->fileValidators as $oneValidator) {
            $oneValidator->validateFile($filePath);
        }
    }
}
