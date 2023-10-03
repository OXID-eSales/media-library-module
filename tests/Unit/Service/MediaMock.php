<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Unit\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSize;
use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;

class MediaMock extends \OxidEsales\MediaLibrary\Service\Media
{
    public function createThumbnail($sFileName, ?ImageSizeInterface $imageSize = null, $thumbnailCrop = true)
    {
        $sFilePath = $this->getMediaPath($sFileName);

        if (is_readable($sFilePath)) {
            if (!$imageSize instanceof ImageSizeInterface) {
                $iSize = $this->getDefaultThumbnailSize();
                $imageSize = new ImageSize($iSize, $iSize);
            }

            $sThumbName = $this->getThumbName($sFileName, $imageSize);

            copy($sFilePath, $this->getThumbnailPath($sThumbName));

            return $sThumbName;
        }

        return false;
    }

    protected function moveUploadedFile($sSourcePath, array|string $sDestPath): bool
    {
        return rename($sSourcePath, $sDestPath);
    }

    protected function getImageSize(array|string $sDestPath): array|false
    {
        return [
            'width' => '300',
            'height' => '300',
            'type' => IMAGETYPE_JPEG,
            'attr' => 'height="300" width="300"'
        ];
    }
}
