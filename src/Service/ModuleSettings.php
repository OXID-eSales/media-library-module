<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\MediaLibrary\Core\Module;

/**
 * @extendable-class
 */
class ModuleSettings
{
    /** Other Settings */
    public const MEDIALIBRARY_ALTERNATIVE_IMAGE_DIRECTORY = 'ddoeMediaLibraryAlternativeImageDirectory';

    public function __construct(
        private ModuleSettingServiceInterface $moduleSettingService
    ) {
    }

    /**
     * Other Settings
     */

    public function getAlternativeImageDirectory(): string
    {
        return $this->getStringSettingValue(self::MEDIALIBRARY_ALTERNATIVE_IMAGE_DIRECTORY);
    }

    protected function getStringSettingValue($key): string
    {
        return $this->moduleSettingService->getString(
            $key,
            Module::MODULE_ID
        )->trim()->toString();
    }
}
