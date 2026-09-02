<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Service;

use OxidEsales\MediaLibrary\Media\Exception\MediaDeletionErrorException;

interface MediaDeletionPolicyServiceInterface
{
    /**
     * @throws MediaDeletionErrorException
     */
    public function validateMediaDeletion(array $ids): void;
}
