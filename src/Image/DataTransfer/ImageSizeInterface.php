<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\DataTransfer;

interface ImageSizeInterface
{
    public function getWidth(): int;
    public function getHeight(): int;

    public function isEmpty(): bool;

    public function getInFormat(string $format, string $emptyFormat): string;
}
