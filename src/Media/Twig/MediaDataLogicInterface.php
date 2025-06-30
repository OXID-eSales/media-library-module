<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Media\Twig;

interface MediaDataLogicInterface
{
    public function getMediaUrl(string $mediaId): string;
}
