<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Service;

use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\MediaLibrary\Exception\WrongFileTypeException;
use OxidEsales\MediaLibrary\Language\Core\LanguageInterface;

class NamingService implements NamingServiceInterface
{
    public function __construct(
        private LanguageInterface $language,
        private ShopAdapterInterface $shopAdapter,
    ) {
    }

    public function sanitizeFilename(string $fileNameInput): string
    {
        $fileName = pathinfo($fileNameInput, PATHINFO_FILENAME);
        $fileExtension = pathinfo($fileNameInput, PATHINFO_EXTENSION);

        $seoCharacters = $this->language->getSeoReplaceChars();
        $fileName = str_replace(array_keys($seoCharacters), array_values($seoCharacters), $fileName);

        //todo: allow dot?
        $fileName = preg_replace('/[^a-zA-Z0-9-_]+/', '-', $fileName);

        return $fileName . ($fileExtension ? '.' . $fileExtension : '');
    }

    public function getUniqueFilename(string $path): string
    {
        while (file_exists($path)) {
            $path = $this->findNextPossibleFilename($path);
        }

        return $path;
    }

    private function findNextPossibleFilename(string $path): string
    {
        $pathInfo = pathinfo($path);
        $newFileName = $pathInfo['filename'] . '_1';

        if (preg_match('/(?P<baseFilename>.+)_(?P<numericPart>\d+)$/', $pathInfo['filename'], $matches)) {
            $newFileName = $matches['baseFilename'] . '_' . ++$matches['numericPart'];
        }

        return $pathInfo['dirname']
            . DIRECTORY_SEPARATOR . $newFileName
            . (isset($pathInfo['extension']) && $pathInfo['extension'] ? '.' . $pathInfo['extension'] : '');
    }

    public function getUniqueId(): string
    {
        return $this->shopAdapter->generateUniqueId();
    }
}
