<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Service;

use Symfony\Component\Filesystem\Path;

class FallbackMediaResource implements FallbackMediaResourceInterface
{
    public const MEDIA_ID = 'ddoemedialibraryfallbackimage001';

    private const FILE_NAME = 'nopic.jpg';
    private const PATH_TO_MODULE_ROOT = '/../../..';

    public function getMediaId(): string
    {
        return self::MEDIA_ID;
    }

    public function getSourcePath(): string
    {
        return Path::canonicalize(__DIR__ . self::PATH_TO_MODULE_ROOT . '/assets/' . self::FILE_NAME);
    }

    public function getFileName(): string
    {
        return self::FILE_NAME;
    }
}
