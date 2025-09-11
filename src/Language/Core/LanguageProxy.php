<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Language\Core;

class LanguageProxy implements LanguageInterface
{
    /** @var LanguageExtension $language */
    private $language;

    public function __construct(\OxidEsales\Eshop\Core\Language $language)
    {
        /** @var LanguageExtension $language */
        $this->language = $language;
    }

    public function getLanguageStringsArray(): array
    {
        $tplLanguage = (int)$this->language->getTplLanguage();
        return $this->language->getLanguageStrings($tplLanguage);
    }

    public function getSeoReplaceChars(): array
    {
        $editLanguage = (int)$this->language->getEditLanguage();
        /** @var array $seoChars */
        $seoChars = $this->language->getSeoReplaceChars($editLanguage);
        return $seoChars;
    }

    public function getLanguageArray(): array
    {
        return $this->language->getLanguageArray();
    }
}
