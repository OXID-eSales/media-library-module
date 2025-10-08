<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\DataType;

class MediaAltText implements MediaAltTextInterface
{
    public function __construct(
        private readonly string $objectId,
        private readonly int $languageId,
        private readonly string $text
    ) {
    }

    public function getObjectId(): string
    {
        return $this->objectId;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
