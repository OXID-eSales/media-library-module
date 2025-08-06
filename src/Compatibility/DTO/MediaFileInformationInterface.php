<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Compatibility\DTO;

interface MediaFileInformationInterface
{
    public function getFileName(): string;

    public function getFolderName(): string;
}
