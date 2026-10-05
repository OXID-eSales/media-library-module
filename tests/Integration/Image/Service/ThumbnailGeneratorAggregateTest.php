<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace Image\Service;

use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailGeneratorAggregate;
use OxidEsales\MediaLibrary\Image\Service\ThumbnailGeneratorAggregateInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ThumbnailGeneratorAggregate::class)]
class ThumbnailGeneratorAggregateTest extends IntegrationTestCase
{
    public function testInitialization(): void
    {
        $sut = $this->getSut();
        $this->assertInstanceOf(ThumbnailGeneratorAggregateInterface::class, $sut);
    }

    private function getSut(): ThumbnailGeneratorAggregateInterface
    {
        return $this->get(ThumbnailGeneratorAggregateInterface::class);
    }
}
