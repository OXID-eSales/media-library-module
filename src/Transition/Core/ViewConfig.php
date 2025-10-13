<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Transition\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\MediaLibrary\Media\Service\MediaResourceInterface;

/**
 * Class ViewConfig
 *
 * @mixin \OxidEsales\Eshop\Core\ViewConfig
 */
class ViewConfig extends ViewConfig_parent
{
    public function getMediaUrl(): string
    {
        $mediaResource = $this->getService(MediaResourceInterface::class);
        return $mediaResource->getUrlToMediaFiles();
    }

    /*
     * Temporary method that adds modification time to the file url
     * todo: remove when script logic changes to allow type="module"
     */
    public function formJsFileUrl(string $fileUrl): string
    {
        $config = Registry::getConfig();
        $filePath = str_replace(
            rtrim($config->getCurrentShopUrl(false), '/'),
            rtrim($config->getConfigParam('sShopDir'), '/'),
            $fileUrl
        );

        $modificationTime = '';
        if (file_exists($filePath)) {
            $modificationTime = filemtime($filePath);
        }

        return $fileUrl . '?' . $modificationTime;
    }
}
