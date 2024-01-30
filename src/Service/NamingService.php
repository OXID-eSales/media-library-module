<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Service;

use OxidEsales\MediaLibrary\Exception\WrongFileTypeException;
use OxidEsales\MediaLibrary\Language\Core\LanguageInterface;

class NamingService implements NamingServiceInterface
{
    protected array $fileExtensionBlacklistRegex = [
        'php.*',
        'exe',
        'js',
        'jsp',
        'cgi',
        'cmf',
        'pht.*',
        'phar',
    ];

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

        if (preg_match('/(?P<baseFilename>.+)_(?P<numericPart>[0-9]+)$/', $pathInfo['filename'], $matches)) {
            $newFileName = $matches['baseFilename'] . '_' . ++$matches['numericPart'];
        } else {
            $newFileName = $pathInfo['filename'] . '_1';
        }

        return $pathInfo['dirname']
            . DIRECTORY_SEPARATOR . $newFileName
            . (isset($pathInfo['extension']) && $pathInfo['extension'] ? '.' . $pathInfo['extension'] : '');
    }

    public function validateFileName(string $fileName): bool
    {
        $extension = $this->getFileNameExtension($fileName);

        foreach ($this->fileExtensionBlacklistRegex as $oneExpression) {
            if (preg_match("/{$oneExpression}/si", $extension)) {
                throw new WrongFileTypeException();
            }
        }

        return true;
    }

    public function getFileNameExtension(string $fileName): string
    {
        $fileNameParts = explode(".", $fileName);
        if (count($fileNameParts) < 2) {
            throw new WrongFileTypeException();
        }

        return end($fileNameParts);
    }
}
