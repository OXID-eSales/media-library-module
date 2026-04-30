<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Validator\ContentValidator;

use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Validation\Format\DTO\FileFormatInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;

final class RasterImageContentValidator implements ContentValidatorInterface
{
    private const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'gif', 'png', 'webp', 'avif'];

    public function supports(FileFormatInterface $format): bool
    {
        return in_array($format->getExtension(), self::SUPPORTED_EXTENSIONS, true);
    }

    public function validate(FilePathInterface $filePath): void
    {
        if (@getimagesize($filePath->getPath()) === false) {
            throw new ValidationFailedException('OE_MEDIA_LIBRARY_EXCEPTION_INVALID_IMAGE_CONTENT');
        }
    }
}
