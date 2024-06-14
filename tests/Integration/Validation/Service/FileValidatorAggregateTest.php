<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

use OxidEsales\MediaLibrary\Validation\Service\FileValidatorChainInterface;

/**
 * @covers \OxidEsales\MediaLibrary\Validation\Service\FileValidatorChain
 */
class FileValidatorAggregateTest extends \OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase
{
    public function testInitialization(): void
    {
        $sut = $this->get(FileValidatorChainInterface::class);
        $this->assertInstanceOf(FileValidatorChainInterface::class, $sut);
    }
}
