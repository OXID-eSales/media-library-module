<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Transput\RequestData;

use OxidEsales\MediaLibrary\Media\DataType\UploadedFileInterface;

interface UIRequestInterface
{
    public function isOverlay(): bool;

    public function isPopout(): bool;

    public function getFolderId(): string;

    public function getMediaIds(): array;

    public function getMediaListStartIndex(): int;

    public function getTabName(): string;

    public function getUploadedFile(): UploadedFileInterface;
}
