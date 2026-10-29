<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\Detector;

use DOMDocument;

interface SvgViolationDetectorInterface
{
    public function detect(DOMDocument $document): bool;
}
