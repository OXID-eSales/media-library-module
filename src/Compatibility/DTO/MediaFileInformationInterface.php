<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Compatibility\DTO;

interface MediaFileInformationInterface
{
    public function getFileName(): string;

    public function getFolderName(): string;
}
