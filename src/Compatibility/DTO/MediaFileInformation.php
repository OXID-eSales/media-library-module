<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Compatibility\DTO;

class MediaFileInformation implements MediaFileInformationInterface
{
    public function __construct(
        private readonly string $fileName,
        private readonly string $folderName,
    ) {
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getFolderName(): string
    {
        return $this->folderName;
    }
}
