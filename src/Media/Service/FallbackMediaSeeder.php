<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Media\Service;

use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Media\DataType\Media;
use OxidEsales\MediaLibrary\Media\Exception\MediaNotFoundException;
use OxidEsales\MediaLibrary\Media\Repository\MediaRepositoryInterface;
use OxidEsales\MediaLibrary\Media\Settings\FallbackMediaSettingsInterface;
use OxidEsales\MediaLibrary\Service\FileSystemServiceInterface;

class FallbackMediaSeeder implements FallbackMediaSeederInterface
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly MediaResourceInterface $mediaResource,
        private readonly FileSystemServiceInterface $fileSystemService,
        private readonly FallbackMediaSettingsInterface $fallbackMediaSettings,
        private readonly FallbackMediaResourceInterface $fallbackMediaResource,
    ) {
    }

    public function seedMedia(): void
    {
        $mediaId = $this->fallbackMediaResource->getMediaId();

        if (!$this->mediaExists($mediaId)) {
            $mediaPath = $this->copyShippedAssetIntoMediaDirectory();

            $this->mediaRepository->addMedia(new Media(
                oxid: $mediaId,
                fileName: $mediaPath->getFileName(),
                fileSize: $this->fileSystemService->getFileSize($mediaPath->getPath()),
                fileType: $this->fileSystemService->getMimeType($mediaPath->getPath()),
                imageSize: $this->fileSystemService->getImageSize($mediaPath->getPath()),
            ));
        }

        if ($this->fallbackMediaSettings->getFallbackMediaId() === '') {
            $this->fallbackMediaSettings->saveFallbackMediaId($mediaId);
        }
    }

    private function mediaExists(string $mediaId): bool
    {
        try {
            $this->mediaRepository->getMediaById($mediaId);
            return true;
        } catch (MediaNotFoundException) {
            return false;
        }
    }

    private function copyShippedAssetIntoMediaDirectory(): FilePathInterface
    {
        $this->fileSystemService->ensureDirectory($this->mediaResource->getPathToMediaFiles());

        $mediaPath = $this->mediaResource->getPossibleMediaFilePath(
            fileName: $this->fallbackMediaResource->getFileName()
        );

        $this->fileSystemService->copy($this->fallbackMediaResource->getSourcePath(), $mediaPath->getPath());

        return $mediaPath;
    }
}
