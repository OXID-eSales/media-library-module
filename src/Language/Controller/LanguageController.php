<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Language\Controller;

use OxidEsales\MediaLibrary\Language\Core\LanguageInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

readonly class LanguageController
{
    public function __construct(
        private LanguageInterface $languageService,
    ) {
    }

    #[Route('/api/oeml-translations', methods: ['GET'])]
    public function getTranslations(): JsonResponse
    {
        $translations = $this->languageService->getLanguageStringsArray();

        return new JsonResponse(data: $translations);
    }
}
