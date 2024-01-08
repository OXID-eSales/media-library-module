<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Transput\RequestData;

interface UIRequestInterface
{
    public function isOverlay(): bool;

    public function isPopout(): bool;

    public function getFolderId(): string;

    public function getMediaListStartIndex(): int;

    public function getTabName(): string;
}
