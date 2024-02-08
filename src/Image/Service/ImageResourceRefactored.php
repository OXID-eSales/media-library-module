<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Image\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\MediaLibrary\Image\DataTransfer\FilePath;
use OxidEsales\MediaLibrary\Image\DataTransfer\FilePathInterface;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Service\NamingServiceInterface;
use Symfony\Component\Filesystem\Path;

class ImageResourceRefactored implements ImageResourceRefactoredInterface
{
    public const MEDIA_PATH = 'out/pictures/ddmedia';

    public function __construct(
        protected Config $shopConfig,
        protected NamingServiceInterface $namingService,
    ) {
    }

    public function getPathToMediaFiles(string $folder = ''): string
    {
        return Path::join(
            $this->shopConfig->getConfigParam('sShopDir'),
            self::MEDIA_PATH,
            $folder
        );
    }

    // TODO: Alternative image URL should be handled
    public function getUrlToMedia(string $folder = '', string $fileName = ''): string
    {
        return Path::join(
            $this->shopConfig->getSslShopUrl(),
            self::MEDIA_PATH,
            $folder,
            $fileName
        );
    }

    public function getPathToMediaFile(MediaInterface $media): string
    {
        return $this->getPathToMediaFiles($media->getFolderName()) . '/' . $media->getFileName();
    }

    public function getPossibleMediaFilePath(string $folderName = '', string $fileName = ''): FilePathInterface
    {
        $uniqueFileName = $this->namingService->getUniqueFilename(
            Path::join(
                $this->getPathToMediaFiles($folderName),
                $fileName
            ),
        );

        return new FilePath($uniqueFileName);
    }
}
