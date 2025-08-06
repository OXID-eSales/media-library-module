<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Compatibility\Facade;

use OxidEsales\MediaLibrary\Compatibility\Exception\MediaNotFoundByFileInformationException;
use OxidEsales\MediaLibrary\Compatibility\Exception\UnknownPathFormatException;

interface MediaIdByPathFacadeInterface
{
    /**
     * @throws MediaNotFoundByFileInformationException
     * @throws UnknownPathFormatException
     */
    public function getMediaIdByPath(string $path): string;
}
