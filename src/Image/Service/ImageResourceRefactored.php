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
        protected ImageResourceInterface $oldImageResource,
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

    public function calculateMediaThumbnailUrl(string $fileName, string $fileType): string
    {
        $result = '';

        if ($fileType !== 'directory') {
            $result = $this->oldImageResource->getThumbnailUrl($fileName);
        }

        return $result;
    }
}
