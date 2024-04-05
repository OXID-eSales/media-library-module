<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\Exception\WrongMediaIdGivenException;

class MediaRepository implements MediaRepositoryInterface
{
    private Connection $connection;

    public function __construct(
        private ConnectionProviderInterface $connectionProvider,
        private ContextInterface $context,
        private MediaFactoryInterface $mediaFactory,
    ) {
        $this->connection = $this->connectionProvider->get();
    }

    public function getFolderMediaCount(string $folderId): int
    {
        $result = $this->connection->executeQuery(
            "SELECT count(*) FROM ddmedia WHERE OXSHOPID = :OXSHOPID AND DDFOLDERID = :DDFOLDERID",
            [
                'OXSHOPID' => $this->context->getCurrentShopId(),
                'DDFOLDERID' => $folderId
            ]
        );

        return $result->fetchOne();
    }

    public function getFolderMedia(string $folderId, int $start, int $limit = 18): array
    {
        $queryResult = $this->connection->executeQuery(
            $this->getMediaSelectSqlPart()
            . "WHERE m.OXSHOPID = :OXSHOPID AND m.DDFOLDERID = :DDFOLDERID
            ORDER BY m.OXTIMESTAMP DESC LIMIT $start, $limit",
            [
                'OXSHOPID' => $this->context->getCurrentShopId(),
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
            $this->getMediaSelectSqlPart()
            . "WHERE m.OXID = :OXID",
            [
                'OXID' => $mediaId
            ]
        );

        $data = $result->fetchAssociative();
        if ($data) {
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
                DDIMAGESIZE = :DDIMAGESIZE,
                DDFOLDERID = :DDFOLDERID",
            [
                'OXSHOPID' => $this->context->getCurrentShopId(),
                'OXID' => $exampleMedia->getOxid(),
                'DDFILENAME' => $exampleMedia->getFileName(),
                'DDFILESIZE' => $exampleMedia->getFileSize(),
                'DDFILETYPE' => $exampleMedia->getFileType(),
                'DDIMAGESIZE' => $exampleMedia->getImageSize()->getInFormat("%dx%d", ""),
                'DDFOLDERID' => $exampleMedia->getFolderId()
            ]
        );
    }

    private function getMediaSelectSqlPart(): string
    {
        return "SELECT m.*, j.DDFILENAME as FOLDERNAME FROM ddmedia m
            LEFT JOIN ddmedia j ON j.OXID=m.DDFOLDERID AND m.DDFOLDERID <> ''";
    }

    /**
     * @throws Exception
     */
    public function renameMedia(string $mediaIdToRename, string $newName): MediaInterface
    {
        $this->connection->executeQuery(
            "UPDATE ddmedia SET DDFILENAME = :DDFILENAME WHERE OXID = :OXID",
            [
                'DDFILENAME' => $newName,
                'OXID' => $mediaIdToRename
            ]
        );

        return $this->getMediaById($mediaIdToRename);
    }

    /**
     * @throws WrongMediaIdGivenException
     * @throws Exception
     */
    public function deleteMedia(string $idToRemove): void
    {
        if (!$idToRemove) {
            throw new WrongMediaIdGivenException();
        }

        $this->connection->executeQuery(
            "DELETE FROM ddmedia WHERE OXID = :OXID OR DDFOLDERID = :OXID",
            [
                'OXID' => $idToRemove
            ]
        );
    }

    public function changeMediaFolderId(string $mediaIdToUpdate, string $newFolderId): void
    {
        $this->connection->executeQuery(
            "UPDATE ddmedia SET DDFOLDERID = :DDFOLDERID WHERE OXID = :OXID",
            [
                'DDFOLDERID' => $newFolderId,
                'OXID' => $mediaIdToUpdate
            ]
        );
    }
}
