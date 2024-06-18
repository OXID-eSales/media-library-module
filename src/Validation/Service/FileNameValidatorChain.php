<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Service;

use OxidEsales\MediaLibrary\Media\DataType\FilePath;
use OxidEsales\MediaLibrary\Validation\Exception\ChainInputTypeException;
use OxidEsales\MediaLibrary\Validation\Validator\FilePathValidatorInterface;

class FileNameValidatorChain implements FileNameValidatorChainInterface
{
    /**
     * @param iterable<FilePathValidatorInterface> $fileValidators
     * @throws ChainInputTypeException
     */
    public function __construct(
        private iterable $fileValidators
    ) {
        foreach ($this->fileValidators as $oneValidator) {
            if (!$oneValidator instanceof FilePathValidatorInterface) {
                throw new ChainInputTypeException();
            }
        }
    }

    public function validateFileName(string $fileName): void
    {
        $filePath = new FilePath($fileName);

        foreach ($this->fileValidators as $oneValidator) {
            $oneValidator->validateFile($filePath);
        }
    }
}
