<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Service;

interface FallbackMediaResourceInterface
{
    public function getMediaId(): string;

    public function getSourcePath(): string;

    public function getFileName(): string;
}
