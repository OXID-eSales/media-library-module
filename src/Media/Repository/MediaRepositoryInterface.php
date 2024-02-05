<?php

namespace OxidEsales\MediaLibrary\Media\Repository;

use Doctrine\DBAL\Exception;
use OxidEsales\MediaLibrary\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Exception\WrongMediaIdGivenException;

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

    public function addMedia(MediaInterface $exampleMedia): void;

    public function rename(string $mediaIdToRename, string $newName): void;

    /**
     * @throws WrongMediaIdGivenException
     */
    public function deleteMedia(string $idToRemove): void;
}
