<?php

namespace OxidEsales\MediaLibrary\Media\Repository;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
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

    public function renameMedia(string $mediaIdToRename, string $newName): MediaInterface;

    /**
     * @throws WrongMediaIdGivenException
     */
    public function deleteMedia(string $idToRemove): void;

    public function changeMediaFolderId(string $mediaIdToUpdate, string $newFolderId): void;
}
