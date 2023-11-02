<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\DataTransfer;

class ImageSize implements ImageSizeInterface
{
    public function __construct(private readonly int $width, private readonly int $height)
    {
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function isEmpty(): bool
    {
        return !$this->getWidth() || !$this->getHeight();
    }

    public function getInFormat(string $format, string $emptyFormat): string
    {
        $finalFormat = $this->isEmpty() ? $emptyFormat : $format;
        return sprintf($finalFormat, $this->getWidth(), $this->getHeight());
    }
}
