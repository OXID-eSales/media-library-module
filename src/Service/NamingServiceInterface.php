<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Service;

interface NamingServiceInterface
{
    public function sanitizeFilename(string $fileNameInput): string;

    public function getUniqueFilename(string $path): string;

    public function validateFileName(string $fileName): bool;

    public function getUniqueId(): string;
}
