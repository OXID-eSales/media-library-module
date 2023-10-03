<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Repository;

use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class MediaRepository implements MediaRepositoryInterface
{
    public function __construct(
        private QueryBuilderFactoryInterface $queryBuilderFactory
    ){}

    public function getShopFolderMediaCount(int $shopId, string $folderId): int
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $result = $queryBuilder->select("count(*)")
            ->from("ddmedia")
            ->where('OXSHOPID = :OXSHOPID and DDFOLDERID = :DDFOLDERID')
            ->setParameters([
                'OXSHOPID' => $shopId,
                'DDFOLDERID' => $folderId
            ])
            ->execute();

        return $result->fetchOne();
    }
}
