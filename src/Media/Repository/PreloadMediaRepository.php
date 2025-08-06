<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Repository;

use Doctrine\DBAL\Connection;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;

class PreloadMediaRepository implements PreloadMediaRepositoryInterface
{
    protected array $idsToPreload = [];

    /** @var array<string, MediaInterface> */
    protected array $preloadedMedia = [];

    public function __construct(
        private readonly Connection $connection,
        private readonly MediaFactoryInterface $mediaFactory,
    ) {
    }

    public function registerForPreload(string ...$mediaIds): void
    {
        foreach ($mediaIds as $oneMediaId) {
            $this->registerOneMediaIdForPreload($oneMediaId);
        }
    }

    private function registerOneMediaIdForPreload(string $oneMediaId): void
    {
        if (!in_array($oneMediaId, $this->idsToPreload) && !isset($this->preloadedMedia[$oneMediaId])) {
            $this->idsToPreload[] = $oneMediaId;
        }
    }

    /**
     * @inheritDoc
     */
    public function getMediaById(string $mediaId): MediaInterface
    {
        $this->registerForPreload($mediaId);
        $this->preload();

        return $this->getPreloadedMedia($mediaId);
    }

    private function preload(): void
    {
        if (empty($this->idsToPreload)) {
            return;
        }

        $placeholders = trim(str_repeat('?,', count($this->idsToPreload)), ',');
        $result = $this->connection->executeQuery(
            $this->getMediaSelectSqlPart() . " WHERE m.OXID in ($placeholders)",
            $this->idsToPreload
        );

        $this->idsToPreload = [];

        while ($data = $result->fetchAssociative()) {
            $this->preloadedMedia[(string)$data['OXID']] = $this->mediaFactory->fromDatabaseArray($data);
        }
    }

    private function getPreloadedMedia(string $mediaId): MediaInterface
    {
        if (!isset($this->preloadedMedia[$mediaId])) {
            throw new MediaNotFoundException(sprintf('Media with id "%s" not found.', $mediaId));
        }

        return $this->preloadedMedia[$mediaId];
    }

    private function getMediaSelectSqlPart(): string
    {
        return "SELECT m.*, j.DDFILENAME as FOLDERNAME FROM ddmedia m
            LEFT JOIN ddmedia j ON j.OXID=m.DDFOLDERID AND m.DDFOLDERID <> ''";
    }
}
