<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Breadcrumb\Service;

use OxidEsales\MediaLibrary\Breadcrumb\DataType\Breadcrumb;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Transput\RequestData\UIRequestInterface;

class BreadcrumbService implements BreadcrumbServiceInterface
{
    public function __construct(
        private UIRequestInterface $request,
        private MediaRepositoryInterface $mediaRepository
    ) {
    }

    public function getBreadcrumbsByRequest(): array
    {
        $result = [];
        $folderId = $this->request->getFolderId();

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
