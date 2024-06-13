<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Transput;

use OxidEsales\Eshop\Core\Utils;
use OxidEsales\MediaLibrary\Transput\Response;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Transput\Response
 */
class ResponseTest extends TestCase
{
    public function testRespondAsJson(): void
    {
        $exampleData = ['somekey' => 'someValue'];
        $jsonValue = json_encode($exampleData);

        $utilsMock = $this->createMock(Utils::class);
        $utilsMock->expects($this->once())
            ->method('showMessageAndExit')
            ->with($jsonValue);

        $correctHeaderSet = false;
        $utilsMock->method('setHeader')->willReturnCallback(function ($value) use (&$correctHeaderSet) {
            if (preg_match("@Content-Type:\s?application/json;\s?charset=UTF-8@i", $value)) {
                $correctHeaderSet = true;
            }
        });

        $sut = new Response($utilsMock);
        $sut->responseAsJson($exampleData);

        $this->assertTrue($correctHeaderSet);
    }

    public function testErrorRespondAsJson(): void
    {
        $exampleData = ['somekey' => 'someValue'];
        $jsonValue = json_encode($exampleData);
        $code = 123;
        $message = uniqid();

        $utilsMock = $this->createMock(Utils::class);
        $utilsMock->expects($this->once())
            ->method('showMessageAndExit')
            ->with($jsonValue);

        $correctHeaderSet = 0b00;
        $utilsMock->method('setHeader')
            ->willReturnCallback(function ($value) use (&$correctHeaderSet, $code, $message) {
                if (preg_match("@Content-Type:\s?application/json;\s?charset=UTF-8@i", $value)) {
                    $correctHeaderSet |= 0b01;
                }
                if (preg_match("@^HTTP/1.1 $code $message$@i", $value)) {
                    $correctHeaderSet |= 0b10;
                }
            });

        $sut = new Response($utilsMock);
        $sut->errorResponseAsJson($code, $message, $exampleData);

        $this->assertSame(0b11, $correctHeaderSet);
    }

    public function testRespondAsJavaScript(): void
    {
        $exampleData = 'someJavaScriptCodeExample';

        $utilsMock = $this->createMock(Utils::class);
        $utilsMock->expects($this->once())
            ->method('showMessageAndExit')
            ->with($exampleData);

        $correctHeaderSet = false;
        $utilsMock->method('setHeader')->willReturnCallback(function ($value) use (&$correctHeaderSet) {
            if (preg_match("@Content-Type:\s?application/javascript;\s?charset=UTF-8@i", $value)) {
                $correctHeaderSet = true;
            }
        });

        $sut = new Response($utilsMock);
        $sut->responseAsJavaScript($exampleData);

        $this->assertTrue($correctHeaderSet);
    }

    public function testRespondAsText(): void
    {
        $exampleData = 'someTextExample';

        $utilsMock = $this->createMock(Utils::class);
        $utilsMock->expects($this->once())
            ->method('showMessageAndExit')
            ->with($exampleData);

        $correctHeaderSet = false;
        $utilsMock->method('setHeader')->willReturnCallback(function ($value) use (&$correctHeaderSet) {
            if (preg_match("@Content-Type:\s?text/html;\s?charset=UTF-8@i", $value)) {
                $correctHeaderSet = true;
            }
        });

        $sut = new Response($utilsMock);
        $sut->responseAsTextHtml($exampleData);

        $this->assertTrue($correctHeaderSet);
    }
}
