<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Compatibility\Service;

use OxidEsales\MediaLibrary\Compatibility\DTO\MediaFileInformation;
use OxidEsales\MediaLibrary\Compatibility\DTO\MediaFileInformationInterface;

class MediaPathService implements MediaPathServiceInterface
{
    public function getMediaFileInformation(string $path): MediaFileInformationInterface
    {
        preg_match('/\/?((?<foldername>[^\/]+)?\/)?(?<filename>[^\/]+)?$/', $path, $matches);

        return new MediaFileInformation(
            fileName: $matches['filename'] ?? '',
            folderName: $matches['foldername'] ?? '',
        );
    }
}
