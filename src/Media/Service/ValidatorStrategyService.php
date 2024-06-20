<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Service;

use OxidEsales\MediaLibrary\Validation\Service\DocumentNameValidatorChainInterface;

class ValidatorStrategyService implements ValidatorStrategyServiceInterface
{
    public function __construct(
        private MediaServiceInterface $mediaService,
        private DocumentNameValidatorChainInterface $fileNameValidatorChain,
        private DocumentNameValidatorChainInterface $directoryNameValidatorChain
    ) {
    }

    public function getValidatorChainByMediaId(string $exampleMediaId): DocumentNameValidatorChainInterface
    {
        $media = $this->mediaService->getMediaById($exampleMediaId);

        if ($media->isDirectory()) {
            return $this->directoryNameValidatorChain;
        }

        return $this->fileNameValidatorChain;
    }
}
