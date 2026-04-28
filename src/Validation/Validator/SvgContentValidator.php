<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Validator;

use OxidEsales\MediaLibrary\Image\Sanitizer\SvgValidatorInterface;
use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Media\DataType\UploadedFileInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use Psr\Log\LoggerInterface;

final class SvgContentValidator implements FilePathValidatorInterface
{
    public function __construct(
        private readonly SvgValidatorInterface $svgValidator,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function validateFile(FilePathInterface $filePath): void
    {
        if (!$this->isSvgUpload($filePath)) {
            return;
        }

        $content = @file_get_contents($filePath->getPath());
        if ($content === false || $content === '') {
            throw new ValidationFailedException('OE_MEDIA_LIBRARY_EXCEPTION_FILE_NOT_UPLOADED');
        }

        try {
            $this->svgValidator->validate($content);
        } catch (ValidationFailedException $exception) {
            $this->logger->error(
                'Rejected SVG upload: contains disallowed content',
                [
                    'file' => $filePath->getFileName(),
                    'reason' => $exception->getMessage(),
                ]
            );
            throw $exception;
        }
    }

    private function isSvgUpload(FilePathInterface $filePath): bool
    {
        if (!$filePath instanceof UploadedFileInterface) {
            return false;
        }

        return strtolower(pathinfo($filePath->getFileName(), PATHINFO_EXTENSION)) === 'svg';
    }
}
