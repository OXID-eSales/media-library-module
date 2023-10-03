<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Integration\Service;

class MediaMock extends \OxidEsales\MediaLibrary\Service\Media
{
    protected function moveUploadedFile($sSourcePath, array|string $sDestPath): bool
    {
        $sSourcePath = realpath($sSourcePath);

        $result = rename($sSourcePath, $sDestPath);

        return $result;
    }
}
