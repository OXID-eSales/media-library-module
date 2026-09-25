<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\DataType;

class MediaLookupContext implements MediaLookupContextInterface
{
    public function __construct(
        private readonly string $trigger,
        private readonly string $identifier = '',
        private readonly string $note = ''
    ) {
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getNote(): string
    {
        return $this->note;
    }
}
