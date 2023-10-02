<?php

namespace OxidEsales\MediaLibrary\Transition\Core;

use OxidEsales\MediaLibrary\Transition\Core\Language;

class LanguageProxy implements LanguageInterface
{
    /** @var Language $language */
    private $language;

    public function __construct(\OxidEsales\Eshop\Core\Language $language)
    {
        /** @var Language $language */
        $this->language = $language;
    }

    public function getLanguageStringsArray(): array
    {
        return $this->language->getLanguageStrings();
    }
}