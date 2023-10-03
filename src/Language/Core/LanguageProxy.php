<?php

namespace OxidEsales\MediaLibrary\Language\Core;

use OxidEsales\MediaLibrary\Language\Core\LanguageExtension;

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
        return $this->language->getLanguageStrings();
    }
}
