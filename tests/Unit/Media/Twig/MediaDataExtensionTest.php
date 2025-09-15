<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\Twig;

use OxidEsales\MediaLibrary\Media\Twig\MediaDataExtension;
use OxidEsales\MediaLibrary\Media\Twig\MediaDataLogicInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class MediaDataExtensionTest extends TestCase
{
    #[Test]
    public function twigFunctionsAreRegistered(): void
    {
        $sut = new MediaDataExtension(
            container: $containerMock = $this->createMock(ContainerInterface::class),
        );

        $containerMock->method('get')
            ->with(MediaDataLogicInterface::class)
            ->willReturn($logicStub = $this->createStub(MediaDataLogicInterface::class));

        $functions = $sut->getFunctions();
        $functionMap = [];
        foreach ($functions as $function) {
            $functionMap[$function->getName()] = $function;
        }

        $this->assertArrayHasKey('oeMediaUrl', $functionMap);
        $this->assertSame([$logicStub, 'getMediaUrl'], $functionMap['oeMediaUrl']->getCallable());

        $this->assertArrayHasKey('oeMediaAlt', $functionMap);
        $this->assertSame([$logicStub, 'getMediaAltText'], $functionMap['oeMediaAlt']->getCallable());
    }
}
