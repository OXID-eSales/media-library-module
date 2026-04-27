<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\Sanitizer;

use DOMDocument;
use OxidEsales\MediaLibrary\Image\Sanitizer\Detector\SvgViolationDetectorInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;

final class SvgValidator implements SvgValidatorInterface
{
    /**
     * @param iterable<SvgViolationDetectorInterface> $detectors
     */
    public function __construct(private readonly iterable $detectors)
    {
    }

    public function validate(string $svgContent): void
    {
        $document = new DOMDocument();
        $document->loadXML($svgContent, LIBXML_NONET);

        foreach ($this->detectors as $detector) {
            if ($detector->detect($document)) {
                throw new ValidationFailedException(
                    'OE_MEDIA_LIBRARY_EXCEPTION_SVG_DISALLOWED_CONTENT'
                );
            }
        }
    }
}
