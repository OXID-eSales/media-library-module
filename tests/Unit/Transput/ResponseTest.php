<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Transput;

use OxidEsales\Eshop\Core\Utils;
use OxidEsales\MediaLibrary\Exception\ResponseCreationException;
use OxidEsales\MediaLibrary\Transput\Response;
use OxidEsales\MediaLibrary\Transput\ResponseInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Response::class)]
class ResponseTest extends TestCase
{
    #[Test]
    public function respondAsJson(): void
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

        $sut = $this->getSut(
            utils: $utilsMock,
        );
        $sut->responseAsJson($exampleData);

        $this->assertTrue($correctHeaderSet);
    }

    #[Test]
    public function responseAsJsonExplodesWithExceptionIfJsonEncodeHadAProblem(): void
    {
        $data = ["text" => "\xB1\x31"]; // Invalid UTF-8 bytes

        $sut = $this->getSut();

        $this->expectException(ResponseCreationException::class);
        $sut->responseAsJson($data);
    }

    #[Test]
    public function errorRespondAsJson(): void
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

        $sut = $this->getSut(
            utils: $utilsMock,
        );
        $sut->errorResponseAsJson($code, $message, $exampleData);

        $this->assertSame(0b11, $correctHeaderSet);
    }

    #[Test]
    public function respondAsJavaScript(): void
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

        $sut = $this->getSut(
            utils: $utilsMock,
        );
        $sut->responseAsJavaScript($exampleData);

        $this->assertTrue($correctHeaderSet);
    }

    #[Test]
    public function respondAsText(): void
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

        $sut = $this->getSut(
            utils: $utilsMock,
        );
        $sut->responseAsTextHtml($exampleData);

        $this->assertTrue($correctHeaderSet);
    }

    private function getSut(
        ?Utils $utils = null,
    ): ResponseInterface {
        $utils ??= $this->createStub(Utils::class);

        return new Response(
            utils: $utils,
        );
    }
}
