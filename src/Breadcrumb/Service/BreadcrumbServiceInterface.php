<?php

namespace OxidEsales\MediaLibrary\Breadcrumb\Service;

use OxidEsales\MediaLibrary\Breadcrumb\DataType\BreadcrumbInterface;

interface BreadcrumbServiceInterface
{
    /**
     * @return array<BreadcrumbInterface>
     */
    public function getBreadcrumbsByRequest(): array;
}