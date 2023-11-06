<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Service;

use OxidEsales\MediaLibrary\Language\Core\LanguageInterface;

class NamingService implements NamingServiceInterface
{
    public function __construct(
        private LanguageInterface $language
    ) {
    }

    public function sanitizeFilename(string $fileNameInput): string
    {
        $fileName = pathinfo($fileNameInput, PATHINFO_FILENAME);
        $fileExtension = pathinfo($fileNameInput, PATHINFO_EXTENSION);

        $seoCharacters = $this->language->getSeoReplaceChars();
        $fileName = str_replace(array_keys($seoCharacters), array_values($seoCharacters), $fileName);

        $fileName = preg_replace('/[^a-zA-Z0-9-_]+/', '-', $fileName);

        return $fileName . ($fileExtension ? '.' . $fileExtension : '');
    }
}
