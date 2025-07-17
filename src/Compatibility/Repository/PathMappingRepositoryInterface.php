<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Compatibility\Repository;

use OxidEsales\MediaLibrary\Compatibility\Exception\MediaByPathNotFoundException;

interface PathMappingRepositoryInterface
{
    /**
     * @throws MediaByPathNotFoundException
     */
    public function getMediaIdByPath(string $filePath): string;
}
