<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Language\Core;

interface LanguageInterface
{
    public function getLanguageStringsArray(): array;

    public function getSeoReplaceChars(): array;
}
