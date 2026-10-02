<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Service;

use OxidEsales\MediaLibrary\Media\DataType\Media as MediaDataType;

/**
 * @todo-medium move to relevant domain
 */
interface FolderServiceInterface
{
    public function createCustomDir(string $folderName): MediaDataType;
}
