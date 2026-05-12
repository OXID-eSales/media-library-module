<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Validator\ContentValidator;

use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Validation\Format\FileFormatRegistryInterface;
use OxidEsales\MediaLibrary\Validation\Validator\FilePathValidatorInterface;

final class ContentValidatorChain implements FilePathValidatorInterface
{
    /**
     * @param iterable<ContentValidatorInterface> $contentValidators
     */
    public function __construct(
        private readonly FileFormatRegistryInterface $registry,
        private readonly iterable $contentValidators,
    ) {
    }

    public function validateFile(FilePathInterface $filePath): void
    {
        $format = $this->registry->findByExtension($filePath->getExtension());
        if ($format === null) {
            return;
        }

        foreach ($this->contentValidators as $contentValidator) {
            if ($contentValidator->supports($format)) {
                $contentValidator->validate($filePath);
            }
        }
    }
}
