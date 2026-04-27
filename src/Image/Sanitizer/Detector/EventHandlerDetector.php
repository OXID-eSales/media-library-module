<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\Sanitizer\Detector;

use DOMDocument;
use DOMXPath;

final class EventHandlerDetector implements SvgViolationDetectorInterface
{
    public function detect(DOMDocument $document): bool
    {
        $xpath = new DOMXPath($document);
        $attributes = $xpath->query('//@*[starts-with(local-name(), "on")]');

        return $attributes !== false && $attributes->length > 0;
    }
}
