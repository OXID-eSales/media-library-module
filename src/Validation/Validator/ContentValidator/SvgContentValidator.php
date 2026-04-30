<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Validator\ContentValidator;

use OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\SvgScannerInterface;
use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Media\DataType\UploadedFileInterface;
use OxidEsales\MediaLibrary\Validation\Format\DTO\FileFormatInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use Psr\Log\LoggerInterface;

final class SvgContentValidator implements ContentValidatorInterface
{
    private const SUPPORTED_EXTENSION = 'svg';

    public function __construct(
        private readonly SvgScannerInterface $svgScanner,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function supports(FileFormatInterface $format): bool
    {
        return $format->getExtension() === self::SUPPORTED_EXTENSION;
    }

    public function validate(FilePathInterface $filePath): void
    {
        if (!$filePath instanceof UploadedFileInterface) {
            return;
        }

        $content = @file_get_contents($filePath->getPath());
        if ($content === false || $content === '') {
            throw new ValidationFailedException('OE_MEDIA_LIBRARY_EXCEPTION_FILE_NOT_UPLOADED');
        }

        try {
            $this->svgScanner->scan($content);
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
}
