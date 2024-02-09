<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Service;

use OxidEsales\MediaLibrary\Media\DataType\MediaInterface as MediaDataType;

interface MediaServiceInterface
{
    /**
     * TODO: Remove this
     * @deprecated Do not use. Will be removed asap
     */
    public function createDirs();

    public function upload(string $uploadedFilePath, string $folderId, string $fileName): MediaDataType;

    public function rename(string $mediaId, string $newMediaName): MediaDataType;

    public function moveToFolder(string $mediaId, string $folderId): void;

    public function delete(array $ids): void;

    public function deleteMedia(MediaDataType $media): void;
}
