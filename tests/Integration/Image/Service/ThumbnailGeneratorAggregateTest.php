<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Integration\Image\Service;

use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailGeneratorAggregate;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailGeneratorAggregateInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ThumbnailGeneratorAggregate::class)]
class ThumbnailGeneratorAggregateTest extends IntegrationTestCase
{
    #[Test]
    public function initialization(): void
    {
        $sut = $this->getSut();
        $this->assertInstanceOf(ThumbnailGeneratorAggregateInterface::class, $sut);
    }

    private function getSut(): ThumbnailGeneratorAggregateInterface
    {
        return $this->get(ThumbnailGeneratorAggregateInterface::class);
    }
}
