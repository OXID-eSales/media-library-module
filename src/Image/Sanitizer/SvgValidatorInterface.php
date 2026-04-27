<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\Sanitizer;

use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;

interface SvgValidatorInterface
{
    /**
     * @throws ValidationFailedException
     */
    public function validate(string $svgContent): void;
}
