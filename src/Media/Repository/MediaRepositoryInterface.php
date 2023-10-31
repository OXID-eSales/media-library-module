<?php

namespace OxidEsales\MediaLibrary\Media\Repository;

use OxidEsales\MediaLibrary\Media\DataType\Media;

interface MediaRepositoryInterface
{
    public function getFolderMediaCount(string $folderId): int;

    /**
     * @return array<Media>
     */
    public function getFolderMedia(string $folderId, int $start, int $limit = 18): array;
}
