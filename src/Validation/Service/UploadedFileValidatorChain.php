<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Service;

use OxidEsales\MediaLibrary\Media\DataType\UploadedFileInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ChainInputTypeException;
use OxidEsales\MediaLibrary\Validation\Validator\FilePathValidatorInterface;

class UploadedFileValidatorChain implements UploadedFileValidatorChainInterface
{
    /**
     * @param iterable<FilePathValidatorInterface> $fileValidators
     * @throws ChainInputTypeException
     */
    public function __construct(
        /** @var iterable<FilePathValidatorInterface|object> */
        private iterable $fileValidators
    ) {
        foreach ($this->fileValidators as $oneValidator) {
            if (!$oneValidator instanceof FilePathValidatorInterface) {
                throw new ChainInputTypeException();
            }
        }
    }

    public function validateFile(UploadedFileInterface $uploadedFile): void
    {
        foreach ($this->fileValidators as $oneValidator) {
            $oneValidator->validateFile($uploadedFile);
        }
    }
}
