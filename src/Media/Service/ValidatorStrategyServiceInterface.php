<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Media\Service;

use OxidEsales\MediaLibrary\Validation\Service\DocumentNameValidatorChainInterface;

interface ValidatorStrategyServiceInterface
{
    public function getValidatorChainByMediaId(string $exampleMediaId): DocumentNameValidatorChainInterface;
}
