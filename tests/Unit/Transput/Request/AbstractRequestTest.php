<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Transput\Request;

use OxidEsales\EshopCommunity\Core\Request;
use OxidEsales\MediaLibrary\Transput\Request\AbstractRequest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Transput\Request\AbstractRequest
 */
class AbstractRequestTest extends TestCase
{
    public function testConstructor(): void
    {
        $requestMock = $this->createMock(\OxidEsales\Eshop\Core\Request::class);

        $sut = new class($requestMock) extends AbstractRequest {
            public function getRequestForTest(): Request
            {
                return $this->request;
            }
        };

        $this->assertSame($requestMock, $sut->getRequestForTest());
    }
}