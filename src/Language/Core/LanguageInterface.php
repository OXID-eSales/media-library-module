<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Language\Core;

interface LanguageInterface
{
    public function getLanguageStringsArray(): array;

    public function getSeoReplaceChars(): array;

    public function getLanguageArray(): array;

    public function getBaseLanguage(): int;
}
