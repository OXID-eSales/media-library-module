<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Image\Service;

use Doctrine\DBAL\Connection;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\MediaLibrary\Service\ModuleSettings;
use Symfony\Component\Filesystem\Path;

class ImageResource implements ImageResourceInterface
{
    protected int $defaultThumbnailSize = 185;
    public const MEDIA_PATH = '/out/pictures/ddmedia/';
    public const MEDIA_PATH_SHORT = '/ddmedia/';
    public string $_sFolderName = '';
    protected Connection $connection;
    public string $_sFolderId = '';

    public function __construct(
        protected Config $shopConfig,
        protected ModuleSettings $moduleSettings,
        protected ThumbnailGeneratorInterface $thumbnailGenerator,
        ConnectionProviderInterface $connectionProvider,
    ) {
        $this->connection = $connectionProvider->get();
    }


    public function getFolderName(): string
    {
        return $this->_sFolderName;
    }

    public function setFolderName($sFolderName): void
    {
        $this->_sFolderName = $sFolderName;
    }

    /**
     * @deprecated This is temporary solution, it should be removed in next release.
     */
    public function getFolderId(): string
    {
        return $this->_sFolderId;
    }

    /**
     * @param $sId
     *
     * @return void
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    private function setFolderNameForFolderId($sId)
    {
        $iShopId = $this->shopConfig->getActiveShop()->getShopId();

        $sSelect = "SELECT `DDFILENAME` FROM `ddmedia` WHERE `OXID` = ? AND `DDFILETYPE` = ? AND `OXSHOPID` = ?";
        $folderName = $this->connection->fetchOne($sSelect, [$sId, 'directory', $iShopId]);
        $sFolderName = $folderName ?: '';

        if ($sFolderName) {
            $this->setFolderName($sFolderName);
        }
    }

    public function setFolder($sFolderId = ''): void
    {
        $this->_sFolderId = $sFolderId;
        if ($sFolderId) {
            $this->setFolderNameForFolderId($sFolderId);
        }
    }

    public function getDefaultThumbnailSize(): int
    {
        return $this->defaultThumbnailSize;
    }

    public function getThumbName($sFile, ?ImageSizeInterface $imageSize = null, $thumbnailCrop = true): string
    {
        $imageSize = $this->getDefaultImageSizeIfNotProvided($imageSize);
        $imageSizeString = sprintf(
            '%d*%d%s%s',
            $imageSize->getWidth(),
            $imageSize->getHeight(),
            $thumbnailCrop ? '' : '_nocrop',
            '.jpg'
        );
        return  str_replace('.', '_', md5(basename($sFile))) . '_thumb_' . $imageSizeString;
    }

    public function getThumbnailUrl($sFile = '', ?ImageSizeInterface $imageSize = null, $thumbnailCrop = true): string
    {
        if (!$sFile) {
            return $this->getMediaUrl('thumbs/');
        }

        $imageSize = $this->getDefaultImageSizeIfNotProvided($imageSize);
        $sThumbName = $this->getThumbName($sFile, $imageSize, $thumbnailCrop);
        if (!is_file($this->getThumbnailPath($sThumbName))) {
            $sThumbName = $this->createThumbnail($sFile, $imageSize, $thumbnailCrop);
        }
        if (is_file($this->getThumbnailPath($sThumbName))) {
            return $this->getMediaUrl('thumbs/' . $sThumbName);
        }

        return '';
    }

    public function getThumbnailPath($filename = ''): string
    {
        return Path::join($this->getMediaPath(), 'thumbs', $filename);
    }

    /**
     * todo: exception in place of bool response
     */
    public function getMediaUrl($filename = '')
    {
        $filepath = $this->getMediaPath($filename);

        if ($this->isAlternativeImageUrlConfigured()) {
            return $filepath;
        }

        if (!is_readable($filepath)) {
            return false;
        }

        if (strpos($filename, 'thumbs/') === false) {
            $filename = basename($filename);
        }

        return Path::join(
            $this->shopConfig->getSslShopUrl(),
            self::MEDIA_PATH,
            (isset($this->_sFolderName) ? $this->_sFolderName . '/' : ''),
            $filename
        );
    }

    public function getMediaPath($filename = '', $blDoNotSetFolder = false): string
    {
        if (!$blDoNotSetFolder) {
            $this->checkAndSetFolderName($filename);
        }

        $sPath = $this->getPathToMediaFiles() . '/' . ($this->_sFolderName ? $this->_sFolderName . '/' : '');

        if ($filename) {
            return $sPath . (strpos($filename, 'thumbs/') !== false ? $filename : basename($filename));
        }

        return $sPath;
    }

    public function createThumbnail($sFileName, ?ImageSizeInterface $imageSize = null, $thumbnailCrop = true)
    {
        $sFilePath = $this->getMediaPath($sFileName, true);
        if (is_readable($sFilePath)) {
            $imageSize = $this->getDefaultImageSizeIfNotProvided($imageSize);
            $sThumbName = $this->getThumbName($sFileName, $imageSize, $thumbnailCrop);
            $thumbnailPath = $this->getThumbnailPath($sThumbName);
            $this->thumbnailGenerator->generateThumbnail($sFilePath, $thumbnailPath, $imageSize, $thumbnailCrop);

            return $sThumbName;
        }

        return false;
    }

    private function isAlternativeImageUrlConfigured(): bool
    {
        return (bool)$this->getAlternativeImageUrl();
    }

    /**
     * @param $sFile
     */
    protected function checkAndSetFolderName($sFile)
    {
        if ($sFile) {
            if (($iPos = strpos($sFile, '/')) !== false) {
                $folderName = substr($sFile, 0, $iPos);
                if ($folderName != 'thumbs') {
                    $this->_sFolderName = substr($sFile, 0, $iPos);
                }
            } else {
                $this->_sFolderName = '';
            }
        }
    }

    private function getPathToMediaFiles(): string
    {
        $basePath = '';
        if ($this->isAlternativeImageUrlConfigured()) {
            $basePath = $this->getAlternativeImageUrl();
            $mediaPath = self::MEDIA_PATH_SHORT;
        } else {
            $basePath = $this->shopConfig->getConfigParam('sShopDir');
            $mediaPath = self::MEDIA_PATH;
        }

        return Path::join($basePath, $mediaPath);
    }

    private function getAlternativeImageUrl(): string
    {
        return $this->moduleSettings->getAlternativeImageDirectory();
    }

    private function getDefaultImageSizeIfNotProvided(?ImageSizeInterface $imageSize = null): ImageSizeInterface
    {
        if (!$imageSize instanceof ImageSizeInterface) {
            $iSize = $this->getDefaultThumbnailSize();
            $imageSize = new ImageSize($iSize, $iSize);
        }
        return $imageSize;
    }
}
