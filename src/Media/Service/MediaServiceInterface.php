<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Service;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface as MediaDataType;
use OxidEsales\MediaLibrary\Media\Exception\MediaDeletionErrorException;

/**
 * @todo-medium We need to segregate more interfaces, so we can decorate those individually
 */
interface MediaServiceInterface
{
    public function upload(string $uploadedFilePath, string $folderId, string $fileName): MediaDataType;

    public function rename(string $mediaId, string $newMediaName): MediaDataType;

    public function moveToFolder(string $mediaId, string $folderId): void;

    /**
     * @throws MediaDeletionErrorException
     */
    public function delete(array $ids): void;

    public function deleteMedia(MediaDataType $media): void;

    public function getMediaById(string $mediaId): MediaDataType;
}
