<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Compatibility\Facade;

use OxidEsales\MediaLibrary\Compatibility\Exception\MediaNotFoundByFileInformationException;

interface MediaIdByPathFacadeInterface
{
    /**
     * @throws MediaNotFoundByFileInformationException
     */
    public function getMediaIdByPath(string $path): string;
}
