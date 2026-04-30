<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Validator\ContentValidator;

use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Validation\Format\DTO\FileFormatInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;

interface ContentValidatorInterface
{
    public function supports(FileFormatInterface $format): bool;

    /**
     * @throws ValidationFailedException
     */
    public function validate(FilePathInterface $filePath): void;
}
