<?php

namespace OxidEsales\MediaLibrary\Breadcrumb\DTO;

interface BreadcrumbInterface
{
    public function getName(): string;

    public function isActive(): bool;
}
