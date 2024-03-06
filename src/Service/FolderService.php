<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Service;

use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\MediaLibrary\Media\DataType\Media as MediaDataType;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Media\Service\MediaResourceInterface;

class FolderService implements FolderServiceInterface
{
    public function __construct(
        protected MediaResourceInterface $mediaResource,
        protected NamingServiceInterface $namingService,
        protected MediaRepositoryInterface $mediaRepository,
        protected FileSystemServiceInterface $fileSystemService,
        protected ShopAdapterInterface $shopAdapter,
    ) {
    }

    public function createCustomDir(string $folderName): MediaDataType
    {
        $folderName = $this->namingService->sanitizeFilename($folderName);

        $folderPath = $this->mediaResource->getPathToMediaFiles($folderName);
        $folderPath = $this->namingService->getUniqueFilename($folderPath);

        $this->fileSystemService->ensureDirectory($folderPath);

        $newMedia = new MediaDataType(
            oxid: $this->shopAdapter->generateUniqueId(),
            fileName: basename($folderPath),
            fileType: 'directory'
        );

        $this->mediaRepository->addMedia($newMedia);

        return $newMedia;
    }
}
