<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Compatibility\Service;

use OxidEsales\MediaLibrary\Compatibility\DTO\MediaFileInformationInterface;

interface MediaPathServiceInterface
{
    public function getMediaFileInformation(string $path): MediaFileInformationInterface;
}
