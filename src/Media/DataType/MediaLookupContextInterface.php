<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\DataType;

interface MediaLookupContextInterface
{
    public function getTrigger(): string;

    public function getIdentifier(): string;

    /** Free-form remark for a human reading the log, not a place for serialized data. */
    public function getNote(): string;
}
