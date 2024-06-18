<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Validator;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;

class FileExtensionValidator implements UploadedFileValidatorInterface
{
    public function validateFile(FilePathInterface $filePath): void
    {
        //TODO: refactor, as this part just extracted with copy/paste
        $aAllowedUploadTypes = (array)Registry::getConfig()->getConfigParam('aAllowedUploadTypes');
        $allowedExtensions = array_map("strtolower", $aAllowedUploadTypes);

        $sSourcePath = $filePath->getFileName();
        $path_parts = pathinfo($sSourcePath);
        $extension = strtolower($path_parts['extension']);
        if (!in_array($extension, $allowedExtensions)) {
            throw new ValidationFailedException("Invalid file extension");
        }
    }
}
