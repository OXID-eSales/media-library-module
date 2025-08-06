<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Compatibility\Repository;

use OxidEsales\MediaLibrary\Compatibility\DTO\MediaFileInformationInterface;
use OxidEsales\MediaLibrary\Compatibility\Exception\MediaNotFoundByFileInformationException;

interface PathMappingRepositoryInterface
{
    /**
     * @throws MediaNotFoundByFileInformationException
     */
    public function getMediaIdByInformation(MediaFileInformationInterface $fileInformation): string;
}
