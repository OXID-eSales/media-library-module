<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Compatibility\Facade;

use OxidEsales\MediaLibrary\Compatibility\Factory\MediaFileInformationFactoryInterface;
use OxidEsales\MediaLibrary\Compatibility\Repository\PathMappingRepositoryInterface;

class MediaIdByPathFacade implements MediaIdByPathFacadeInterface
{
    public function __construct(
        private readonly MediaFileInformationFactoryInterface $mediaFileInformationFactory,
        private readonly PathMappingRepositoryInterface $pathMappingRepository,
    ) {
    }

    public function getMediaIdByPath(string $path): string
    {
        $fileInformation = $this->mediaFileInformationFactory->fromPath($path);
        return $this->pathMappingRepository->getMediaIdByInformation($fileInformation);
    }
}
