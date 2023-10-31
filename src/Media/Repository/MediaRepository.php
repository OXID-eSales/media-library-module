<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Repository;

use Doctrine\DBAL\Connection;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContextInterface;

class MediaRepository implements MediaRepositoryInterface
{
    private Connection $connection;

    public function __construct(
        private ConnectionProviderInterface $connectionProvider,
        private BasicContextInterface $basicContext,
        private MediaFactoryInterface $mediaFactory,
    ) {
        $this->connection = $this->connectionProvider->get();
    }

    public function getFolderMediaCount(string $folderId): int
    {
        $result = $this->connection->executeQuery(
            "SELECT count(*) FROM ddmedia WHERE OXSHOPID = :OXSHOPID AND DDFOLDERID = :DDFOLDERID",
            [
                'OXSHOPID' => $this->basicContext->getCurrentShopId(),
                'DDFOLDERID' => $folderId
            ]
        );

        return $result->fetchOne();
    }

    public function getFolderMedia(string $folderId, int $start, int $limit = 18): array
    {
        $queryResult = $this->connection->executeQuery(
            "SELECT * FROM ddmedia WHERE OXSHOPID = :OXSHOPID AND DDFOLDERID = :DDFOLDERID
            ORDER BY OXTIMESTAMP DESC LIMIT $start, $limit",
            [
                'OXSHOPID' => $this->basicContext->getCurrentShopId(),
                'DDFOLDERID' => $folderId
            ]
        );

        $result = [];
        while ($data = $queryResult->fetchAssociative()) {
            $result[] = $this->mediaFactory->fromDatabaseArray($data);
        }

        return $result;
    }
}
