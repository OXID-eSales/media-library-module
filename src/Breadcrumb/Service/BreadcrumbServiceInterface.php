<?php

namespace OxidEsales\MediaLibrary\Breadcrumb\Service;

use OxidEsales\MediaLibrary\Breadcrumb\DTO\BreadcrumbInterface;

interface BreadcrumbServiceInterface
{
    /**
     * @return array<BreadcrumbInterface>
     */
    public function getBreadcrumbs(string $folderId): array;
}
