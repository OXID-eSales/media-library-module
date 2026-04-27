<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\Sanitizer\Detector;

use DOMDocument;

interface SvgViolationDetectorInterface
{
    public function detect(DOMDocument $document): bool;
}
