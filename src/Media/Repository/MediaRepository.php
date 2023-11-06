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
use OxidEsales\MediaLibrary\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;

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

    public function getMediaById(string $mediaId): MediaInterface
    {
        $result = $this->connection->executeQuery(
            "SELECT * FROM ddmedia WHERE OXSHOPID = :OXSHOPID AND OXID = :OXID",
            [
                'OXSHOPID' => $this->basicContext->getCurrentShopId(),
                'OXID' => $mediaId
            ]
        );

        if ($data = $result->fetchAssociative()) {
            return $this->mediaFactory->fromDatabaseArray($data);
        }

        throw new MediaNotFoundException();
    }

    public function addMedia(MediaInterface $exampleMedia): void
    {
        $this->connection->executeQuery(
            "insert into ddmedia SET 
                OXID = :OXID,
                OXSHOPID = :OXSHOPID,
                DDFILENAME = :DDFILENAME,
                DDFILESIZE = :DDFILESIZE,
                DDFILETYPE = :DDFILETYPE,
                DDTHUMB = :DDTHUMB,
                DDIMAGESIZE = :DDIMAGESIZE,
                DDFOLDERID = :DDFOLDERID",
            [
                'OXSHOPID' => $this->basicContext->getCurrentShopId(),
                'OXID' => $exampleMedia->getOxid(),
                'DDFILENAME' => $exampleMedia->getFileName(),
                'DDFILESIZE' => $exampleMedia->getFileSize(),
                'DDFILETYPE' => $exampleMedia->getFileType(),
                'DDTHUMB' => $exampleMedia->getThumbFileName(),
                'DDIMAGESIZE' => $exampleMedia->getImageSize()->getInFormat("%dx%d", ""),
                'DDFOLDERID' => $exampleMedia->getFolderId()
            ]
        );
    }
}
