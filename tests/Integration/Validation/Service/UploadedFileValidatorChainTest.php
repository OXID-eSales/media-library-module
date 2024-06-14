<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

use OxidEsales\MediaLibrary\Validation\Service\UploadedFileValidatorChainInterface;

/**
 * @covers \OxidEsales\MediaLibrary\Validation\Service\UploadedFileValidatorChain
 */
class UploadedFileValidatorChainTest extends \OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase
{
    public function testInitialization(): void
    {
        $sut = $this->get(UploadedFileValidatorChainInterface::class);
        $this->assertInstanceOf(UploadedFileValidatorChainInterface::class, $sut);
    }
}
