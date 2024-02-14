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

    public function getPathToMediaFiles(string $folderName = ''): string
    {
        return Path::join(
            $this->shopConfig->getConfigParam('sShopDir'),
            self::MEDIA_PATH,
            $folderName
        );
    }

    // TODO: Alternative image URL should be handled
    public function getUrlToMediaFile(string $folderName = '', string $fileName = ''): string
    {
        return Path::join(
            $this->getUrlToMediaFiles($folderName),
            $fileName
        );
    }

    // TODO: Alternative image URL should be handled
    public function getUrlToMediaFiles(string $folderName = ''): string
    {
        return Path::join(
            $this->shopConfig->getSslShopUrl(),
            self::MEDIA_PATH,
            $folderName
        );
    }

    public function getPathToMedia(MediaInterface $media): string
    {
        return $this->getPathToMediaFile($media->getFolderName(), $media->getFileName());
    }

    public function getPathToMediaFile(string $folderName = '', string $fileName = ''): string
    {
        return Path::join(
            $this->getPathToMediaFiles($folderName),
            $fileName
        );
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
