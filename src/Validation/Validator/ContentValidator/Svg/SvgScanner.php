<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg;

use DOMDocument;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\Detector\SvgViolationDetectorInterface;

final class SvgScanner implements SvgScannerInterface
{
    private const DISALLOWED_CONTENT_MESSAGE = 'OE_MEDIA_LIBRARY_EXCEPTION_SVG_DISALLOWED_CONTENT';

    /**
     * @param iterable<SvgViolationDetectorInterface> $detectors
     */
    public function __construct(private readonly iterable $detectors)
    {
    }

    public function scan(string $svgContent): void
    {
        $document = new DOMDocument();
        $document->loadXML($svgContent, LIBXML_NONET);

        foreach ($this->detectors as $detector) {
            if ($detector->detect($document)) {
                throw new ValidationFailedException(self::DISALLOWED_CONTENT_MESSAGE);
            }
        }
    }
}
