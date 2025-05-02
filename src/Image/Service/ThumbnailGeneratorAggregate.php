<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Image\Service;

use OxidEsales\MediaLibrary\Image\Exception\AggregatorInputType;
use OxidEsales\MediaLibrary\Image\Exception\NoSupportedDriversForSource;
use OxidEsales\MediaLibrary\Image\ThumbnailGenerator\ThumbnailGeneratorInterface;

class ThumbnailGeneratorAggregate implements ThumbnailGeneratorAggregateInterface
{
    /** @var iterable<ThumbnailGeneratorInterface> */
    protected iterable $thumbnailGenerators;

    /**
     * @param iterable<ThumbnailGeneratorInterface|object> $thumbnailGenerators
     * @throws AggregatorInputType
     */
    public function __construct(
        iterable $thumbnailGenerators
    ) {
        foreach ($thumbnailGenerators as $oneGenerator) {
            if (!$oneGenerator instanceof ThumbnailGeneratorInterface) {
                throw new AggregatorInputType();
            }
        }

        /** @var iterable<ThumbnailGeneratorInterface> $thumbnailGenerators */
        $this->thumbnailGenerators = $thumbnailGenerators;
    }

    public function getSupportedGenerator(string $sourcePath): ThumbnailGeneratorInterface
    {
        foreach ($this->thumbnailGenerators as $oneDriver) {
            if ($oneDriver->isOriginSupported($sourcePath)) {
                return $oneDriver;
            }
        }

        throw new NoSupportedDriversForSource();
    }
}
