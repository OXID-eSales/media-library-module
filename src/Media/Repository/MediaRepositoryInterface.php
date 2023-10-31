<?php

namespace OxidEsales\MediaLibrary\Media\Repository;

use OxidEsales\MediaLibrary\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\DataType\Media;

interface MediaRepositoryInterface
{
    public function getFolderMediaCount(string $folderId): int;

    /**
     * @return array<Media>
     */
    public function getFolderMedia(string $folderId, int $start, int $limit = 18): array;

    /**
     * @throws MediaNotFoundException
     */
    public function getMediaById(string $mediaId): ?Media;
}
