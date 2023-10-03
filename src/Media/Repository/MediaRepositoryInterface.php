<?php

namespace OxidEsales\MediaLibrary\Media\Repository;

interface MediaRepositoryInterface
{
    public function getShopFolderMediaCount(int $shopId, string $folderId): int;
}