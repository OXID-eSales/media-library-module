<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Transput\RequestData;

use OxidEsales\MediaLibrary\Transput\RequestData\AddFolderRequest;
use OxidEsales\MediaLibrary\Transput\RequestData\AddFolderRequestInterface;
use OxidEsales\MediaLibrary\Transput\RequestInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddFolderRequest::class)]
class AddFolderRequestTest extends TestCase
{
    #[Test]
    public function getName(): void
    {
        $requestExampleValue = uniqid();

        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('getStringRequestParameter')->willReturnMap([
            [AddFolderRequest::REQUEST_PARAM_NAME, '', $requestExampleValue]
        ]);

        $sut = $this->getSut(
            request: $requestMock,
        );
        $this->assertSame($requestExampleValue, $sut->getName());
    }

    private function getSut(
        ?RequestInterface $request = null,
    ): AddFolderRequestInterface {
        $request ??= $this->createStub(RequestInterface::class);

        return new AddFolderRequest(
            request: $request,
        );
    }
}
