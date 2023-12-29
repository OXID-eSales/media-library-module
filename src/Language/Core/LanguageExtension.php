<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Language\Core;

/**
 * @mixin \OxidEsales\Eshop\Core\Language
 */
class LanguageExtension extends LanguageExtension_parent
{
    protected $_aAdditionalLangFiles = [];

    /**
     * @param null|integer $iLang
     * @param null|bool $blAdminMode
     */
    public function getLanguageStrings($iLang = null, $blAdminMode = null): array
    {
        $aLang = [];

        foreach ($this->getLangTranslationArray($iLang, $blAdminMode) as $sLangKey => $sLangValue) {
            $aLang[$sLangKey] = $sLangValue;
        }

        foreach ($this->getLanguageMap($iLang, $blAdminMode) as $sLangKey => $sLangValue) {
            $aLang[$sLangKey] = $sLangValue;
        }

        if (count($this->_aAdditionalLangFiles)) {
            foreach (
                $this->getLangTranslationArray(
                    $iLang,
                    $blAdminMode,
                    $this->_aAdditionalLangFiles
                ) as $sLangKey => $sLangValue
            ) {
                $aLang[$sLangKey] = $sLangValue;
            }
        }

        return $aLang;
    }
}
