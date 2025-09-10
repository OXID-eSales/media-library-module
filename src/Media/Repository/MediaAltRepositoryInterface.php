<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Repository;

use OxidEsales\MediaLibrary\Media\DataType\MediaAltTextInterface;

interface MediaAltRepositoryInterface
{
    public function saveAltText(MediaAltTextInterface $mediaAltText): void;

    /**
     * @return MediaAltTextInterface[]
     */
    public function getObjectAltTexts(string $objectId): array;
}
