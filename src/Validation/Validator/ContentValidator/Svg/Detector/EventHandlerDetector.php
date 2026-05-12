<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\Detector;

use DOMDocument;
use DOMXPath;

final class EventHandlerDetector implements SvgViolationDetectorInterface
{
    public function detect(DOMDocument $document): bool
    {
        $xpath = new DOMXPath($document);
        $attributeList = $xpath->query('//@*[starts-with(local-name(), "on")]');

        return $attributeList instanceof \DOMNodeList && $attributeList->length > 0;
    }
}
