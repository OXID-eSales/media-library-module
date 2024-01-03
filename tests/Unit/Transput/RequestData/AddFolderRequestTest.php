<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Transput\RequestData;

use OxidEsales\MediaLibrary\Transput\RequestData\AddFolderRequest;
use OxidEsales\MediaLibrary\Transput\RequestInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Transput\RequestData\AddFolderRequest
 */
class AddFolderRequestTest extends TestCase
{
    public function testGetName(): void
    {
        $requestExampleValue = uniqid();

        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('getStringRequestParameter')->willReturnMap([
            [AddFolderRequest::REQUEST_PARAM_NAME, '', $requestExampleValue]
        ]);

        $sut = new AddFolderRequest($requestMock);
        $this->assertSame($requestExampleValue, $sut->getName());
    }
}