<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace Image\Service;

use OxidEsales\MediaLibrary\Image\Service\ThumbnailGeneratorAggregateInterface;

/**
 * @covers \OxidEsales\MediaLibrary\Image\Service\ThumbnailGeneratorAggregate
 */
class ThumbnailGeneratorAggregateTest extends \OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase
{
    public function testInitialization(): void
    {
        $sut = $this->get(ThumbnailGeneratorAggregateInterface::class);
        $this->assertInstanceOf(ThumbnailGeneratorAggregateInterface::class, $sut);
    }
}
