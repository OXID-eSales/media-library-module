<?php

/**
 *
 *     |o     o    |          |
 * ,---|.,---..,---|,---.,---.|__/
 * |   |||   |||   ||---'`---.|  \
 * `---'``---|``---'`---'`---'`   `
 *       `---'    [media solutions]
 *
 * @copyright   (c) digidesk - media solutions
 * @link            https://www.digidesk.de
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
