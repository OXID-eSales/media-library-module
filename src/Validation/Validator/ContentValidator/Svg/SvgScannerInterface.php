<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg;

use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;

interface SvgScannerInterface
{
    /**
     * @throws ValidationFailedException
     */
    public function scan(string $svgContent): void;
}
