<?php

namespace OxidEsales\MediaLibrary\Media\Repository;

use OxidEsales\MediaLibrary\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;

interface MediaRepositoryInterface
{
    public function getFolderMediaCount(string $folderId): int;

    /**
     * @return array<MediaInterface>
     */
    public function getFolderMedia(string $folderId, int $start, int $limit = 18): array;

    /**
     * @throws MediaNotFoundException
     */
    public function getMediaById(string $mediaId): ?MediaInterface;
}
