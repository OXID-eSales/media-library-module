<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Compatibility\Factory;

use OxidEsales\MediaLibrary\Compatibility\DTO\MediaFileInformation;
use OxidEsales\MediaLibrary\Compatibility\DTO\MediaFileInformationInterface;
use OxidEsales\MediaLibrary\Compatibility\Exception\UnknownPathFormatException;

class MediaFileInformationFactory implements MediaFileInformationFactoryInterface
{
    public function fromPath(string $path): MediaFileInformationInterface
    {
        if (strstr($path, 'out/pictures/ddmedia/') !== false) {
            preg_match(
                '/out\/pictures\/ddmedia\/((?<foldername>[^\/]+)?\/)?(?<filename>[^\/]+)?$/',
                $path,
                $matches
            );
        } else {
            preg_match(
                '/^((?<foldername>[^\/]+)?\/)?(?<filename>[^\/]+)?$/',
                $path,
                $matches
            );
        }

        if (empty($matches['filename']) && empty($matches['foldername'])) {
            throw new UnknownPathFormatException("Unknown path for media information calculation: $path");
        }

        return new MediaFileInformation(
            fileName: $matches['filename'] ?? '',
            folderName: $matches['foldername'] ?? '',
        );
    }
}
