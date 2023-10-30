<?php

namespace OxidEsales\MediaLibrary\Media\Repository;

use OxidEsales\MediaLibrary\Media\DataType\Media;

interface MediaFactoryInterface
{
    public function fromDatabaseArray(array $item): Media;
}