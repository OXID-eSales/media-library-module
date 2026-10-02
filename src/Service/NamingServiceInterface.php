<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Service;

/**
 * @todo-medium segregate if needed, move to relevant domain
 */
interface NamingServiceInterface
{
    public function sanitizeFilename(string $fileName): string;

    public function getUniqueFilename(string $path): string;

    public function getUniqueId(): string;
}
