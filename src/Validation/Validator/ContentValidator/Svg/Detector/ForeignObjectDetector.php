<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\Detector;

use DOMDocument;
use DOMXPath;

final class ForeignObjectDetector implements SvgViolationDetectorInterface
{
    public function detect(DOMDocument $document): bool
    {
        $xpath = new DOMXPath($document);
        $nodes = $xpath->query('//*[local-name()="foreignObject"]');

        return $nodes !== false && $nodes->length > 0;
    }
}
