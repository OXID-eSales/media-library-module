<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Media\DataType;

interface MediaAltTextInterface
{
    public function getObjectId(): string;
    public function getLanguageId(): int;
    public function getText(): string;
}
