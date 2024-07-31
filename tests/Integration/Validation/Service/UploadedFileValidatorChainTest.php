<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\MediaLibrary\Tests\Integration\Validation\Service;

use OxidEsales\MediaLibrary\Validation\Service\UploadedFileValidatorChain;
use OxidEsales\MediaLibrary\Validation\Service\UploadedFileValidatorChainInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UploadedFileValidatorChain::class)]
class UploadedFileValidatorChainTest extends \OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase
{
    public function testInitialization(): void
    {
        $sut = $this->get(UploadedFileValidatorChainInterface::class);
        $this->assertInstanceOf(UploadedFileValidatorChainInterface::class, $sut);
    }
}
