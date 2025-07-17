<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Compatibility\Factory;

use OxidEsales\MediaLibrary\Compatibility\DTO\MediaFileInformationInterface;

interface MediaFileInformationFactoryInterface
{
    public function fromPath(string $path): MediaFileInformationInterface;
}
