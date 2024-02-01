<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Image\Service;

use OxidEsales\Eshop\Core\Config;
use Symfony\Component\Filesystem\Path;

class ImageResourceRefactored implements ImageResourceRefactoredInterface
{
    public const MEDIA_PATH = 'out/pictures/ddmedia';

    public function __construct(
        protected Config $shopConfig,
    ) {
    }

    public function getPathToMediaFiles(string $subDirectory = ''): string
    {
        return Path::join(
            $this->shopConfig->getConfigParam('sShopDir'),
            self::MEDIA_PATH,
            $subDirectory
        );
    }
}
