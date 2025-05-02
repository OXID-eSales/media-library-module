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

/**
 * @todo: constructor can be reused with UploadedFileValidatorChain
 */
class DocumentNameValidatorChain implements DocumentNameValidatorChainInterface
{
    /** @var iterable<FilePathValidatorInterface> */
    private iterable $fileValidators;

    /**
     * @param iterable<FilePathValidatorInterface|object> $fileValidators
     * @throws ChainInputTypeException
     */
    public function __construct(
        iterable $fileValidators
    ) {
        foreach ($fileValidators as $oneValidator) {
            if (!$oneValidator instanceof FilePathValidatorInterface) {
                throw new ChainInputTypeException();
            }
        }

        /** @var iterable<FilePathValidatorInterface> $fileValidators */
        $this->fileValidators = $fileValidators;
    }

    public function validateDocumentName(string $documentName): void
    {
        $filePath = new FilePath($documentName);

        foreach ($this->fileValidators as $oneValidator) {
            $oneValidator->validateFile($filePath);
        }
    }
}
