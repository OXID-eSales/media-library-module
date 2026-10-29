<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Breadcrumb\Service;

use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\MediaLibrary\Breadcrumb\DTO\Breadcrumb;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;

class BreadcrumbService implements BreadcrumbServiceInterface
{
    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
        private ShopAdapterInterface $shopAdapter,
    ) {
    }

    public function getBreadcrumbs(string $folderId): array
    {
        $result = [];

        $rootName = $this->shopAdapter->translateString('DD_MEDIA_BREADCRUMB_ROOT');
        $result[] = new Breadcrumb(
            name: $rootName,
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
