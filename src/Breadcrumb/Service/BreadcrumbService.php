<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Breadcrumb\Service;

use OxidEsales\MediaLibrary\Breadcrumb\DataType\Breadcrumb;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;

class BreadcrumbService implements BreadcrumbServiceInterface
{
    public function __construct(
        private MediaRepositoryInterface $mediaRepository
    ) {
    }

    public function getBreadcrumbs(string $folderId): array
    {
        $result = [];

        // todo-high: translation for Root
        $result[] = new Breadcrumb(
            name: 'Root',
            active: !$folderId
        );

        if ($folderId) {
            $media = $this->mediaRepository->getMediaById($folderId);
            $result[] = new Breadcrumb(
                name: $media->getFileName(),
                active: true
            );
        }

        return $result;
    }
}
