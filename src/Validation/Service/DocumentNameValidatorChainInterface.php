<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Service;

use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;

interface DocumentNameValidatorChainInterface
{
    /**
     * @throws ValidationFailedException
     */
    public function validateDocumentName(string $documentName): void;
}
