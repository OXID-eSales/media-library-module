<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Image\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
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

    public function getThumbnailFileName(
        string $originalFileName,
        ImageSizeInterface $thumbnailSize,
        bool $crop
    ): string {
        return sprintf(
            '%s_thumb_%d*%d%s%s',
            md5($originalFileName),
            $thumbnailSize->getWidth(),
            $thumbnailSize->getHeight(),
            $crop ? '' : '_nocrop',
            '.jpg'
        );
    }
}
