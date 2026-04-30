<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\Svg\Detector;

use DOMAttr;
use DOMDocument;
use DOMXPath;

final class ScriptableUrlDetector implements SvgViolationDetectorInterface
{
    private const DANGEROUS_SCHEMES = ['javascript:', 'data:'];

    public function detect(DOMDocument $document): bool
    {
        $xpath = new DOMXPath($document);

        foreach ($xpath->query('//@*[local-name()="href"]') ?: [] as $attribute) {
            if ($attribute instanceof DOMAttr && $this->isDangerous($attribute->value)) {
                return true;
            }
        }

        return false;
    }

    private function isDangerous(string $value): bool
    {
        $normalized = strtolower(trim($value));
        foreach (self::DANGEROUS_SCHEMES as $scheme) {
            if (str_starts_with($normalized, $scheme)) {
                return true;
            }
        }
        return false;
    }
}
