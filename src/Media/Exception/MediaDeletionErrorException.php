<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Exception;

class MediaDeletionErrorException extends \Exception
{
    public function __construct(string $message = 'DD_MEDIA_REMOVE_ERR')
    {
        parent::__construct($message);
    }
}
