<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Repository;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Media\DataType\Media;
use OxidEsales\LocaleMapper\Service\LocaleMapperInterface;

class MediaFactory implements MediaFactoryInterface
{
    public function __construct(
        private LocaleMapperInterface $localeMapper
    ) {
    }

    public function fromDatabaseArray(array $item): Media
    {
        $size = explode("x", $item['DDIMAGESIZE']);
        $mediaSize = new ImageSize(intval($size[0] ?: 0), intval($size[1] ?? 0));

        $altTexts = $this->parseTranslations($item['TRANSLATIONS'] ?? '');

        return new Media(
            oxid: (string)$item['OXID'],
            fileName: (string)$item['DDFILENAME'],
            fileSize: (int)$item['DDFILESIZE'],
            fileType: (string)$item['DDFILETYPE'],
            imageSize: $mediaSize,
            folderId: $item['DDFOLDERID'],
            folderName: $item['FOLDERNAME'] ?? '',
            altTexts: $altTexts
        );
    }

    private function parseTranslations(string $translationsString): array
    {
        $translations = [];
        
        if (empty($translationsString)) {
            return $translations;
        }

        // Get the reverse mapping from locale IDs to language IDs 
        $localeIdToLanguageIdMap = $this->localeMapper->getLocaleIdToLanguageIdMap();
        
        $translationPairs = explode('|', $translationsString);
        
        foreach ($translationPairs as $pair) {
            if (empty($pair)) {
                continue;
            }
            
            $parts = explode(':', $pair, 2);
            if (count($parts) === 2) {
                $localeId = $parts[0];
                $altText = $parts[1];
                
                // Convert locale ID back to actual locale using reverse lookup
                if (isset($localeIdToLanguageIdMap[$localeId])) {
                    $languageId = $localeIdToLanguageIdMap[$localeId];
                    try {
                        $locale = $this->localeMapper->getLocaleByLanguageId($languageId);
                        $translations[$locale] = $altText;
                    } catch (\Exception $e) {
                        // If conversion fails, skip this alttext (don't display)
                        continue;
                    }
                }
                // If locale ID not found in map, skip this alttext (don't display)
            }
        }
        
        return $translations;
    }
}
