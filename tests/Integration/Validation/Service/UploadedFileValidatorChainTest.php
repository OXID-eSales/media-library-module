<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Integration\Validation\Service;

use OxidEsales\MediaLibrary\Validation\Service\UploadedFileValidatorChain;
use OxidEsales\MediaLibrary\Validation\Service\UploadedFileValidatorChainInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(UploadedFileValidatorChain::class)]
class UploadedFileValidatorChainTest extends \OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase
{
    #[Test]
    public function initialization(): void
    {
        $sut = $this->getSut();
        $this->assertInstanceOf(UploadedFileValidatorChainInterface::class, $sut);
    }

    private function getSut(): UploadedFileValidatorChainInterface
    {
        return $this->get(UploadedFileValidatorChainInterface::class);
    }
}
