<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\Service;

use OxidEsales\MediaLibrary\Image\DataTransfer\ImageSizeInterface;
use OxidEsales\MediaLibrary\Image\Exception\AggregatorInputType;

class ThumbnailGeneratorAggregate implements ThumbnailGeneratorAggregateInterface
{
    /**
     * @param iterable<ThumbnailGeneratorInterface> $thumbnailGenerators
     * @throws AggregatorInputType
     */
    public function __construct(
        protected iterable $thumbnailGenerators
    ) {
        foreach ($this->thumbnailGenerators as $oneGenerator) {
            if (!$oneGenerator instanceof ThumbnailGeneratorInterface) {
                throw new AggregatorInputType();
            }
        }
    }

    public function generateThumbnail(
        string $sourcePath,
        string $thumbnailPath,
        ImageSizeInterface $thumbnailSize,
        bool $isCropRequired,
    ): void {
        foreach($this->thumbnailGenerators as $oneGenerator) {
            if ($oneGenerator->isOriginSupported($sourcePath)) {
                $oneGenerator->generateThumbnail(
                    sourcePath: $sourcePath,
                    thumbnailPath: $thumbnailPath,
                    size: $thumbnailSize,
                    blCrop: $isCropRequired
                );
                break;
            }
        }
    }
}