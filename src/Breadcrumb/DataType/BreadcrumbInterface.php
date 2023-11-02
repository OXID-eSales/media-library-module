<?php

namespace OxidEsales\MediaLibrary\Breadcrumb\DataType;

interface BreadcrumbInterface
{
    public function getName(): string;

    public function isActive(): bool;
}
