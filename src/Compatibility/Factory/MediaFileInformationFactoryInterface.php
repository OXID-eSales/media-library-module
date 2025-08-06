<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Compatibility\Factory;

use OxidEsales\MediaLibrary\Compatibility\DTO\MediaFileInformationInterface;
use OxidEsales\MediaLibrary\Compatibility\Exception\UnknownPathFormatException;

interface MediaFileInformationFactoryInterface
{
    /**
     * @throws UnknownPathFormatException
     */
    public function fromPath(string $path): MediaFileInformationInterface;
}
