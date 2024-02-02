<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\Service;

interface ImageResourceRefactoredInterface
{
    public function getPathToMediaFiles(string $folder = ''): string;

    public function getUrlToMedia(string $folder = '', string $fileName = ''): string;
}
