<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Validator;

use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Settings\Service\ModuleSettingsInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;

class FileExtensionValidator implements FilePathValidatorInterface
{
    public function __construct(
        private ModuleSettingsInterface $moduleSettings,
    ) {
    }

    public function validateFile(FilePathInterface $filePath): void
    {
        $fileName = $filePath->getFileName();
        $allowedExtensions = $this->moduleSettings->getAllowedExtensions();

        $isSupported = false;
        foreach ($allowedExtensions as $oneExtension) {
            if (preg_match("#\.$oneExtension$#i", $fileName)) {
                $isSupported = true;
                break;
            }
        }

        if (!$isSupported) {
            throw new ValidationFailedException("OE_MEDIA_LIBRARY_EXCEPTION_INVALID_FILE_EXTENTION");
        }
    }
}
