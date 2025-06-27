<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Twig;

use OxidEsales\MediaLibrary\Twig\MediaDataExtension;
use OxidEsales\MediaLibrary\Twig\MediaDataLogicInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class MediaDataExtensionTest extends TestCase
{
    #[Test]
    public function twigFunctionIsRegistered(): void
    {
        $sut = new MediaDataExtension(
            container: $containerMock = $this->createStub(ContainerInterface::class),
        );

        $containerMock->method('get')
            ->with(MediaDataLogicInterface::class)
            ->willReturn($logicStub = $this->createStub(MediaDataLogicInterface::class));

        $functions = $sut->getFunctions();

        $first = reset($functions);
        $this->assertSame('oeMediaUrl', $first->getName());
        $this->assertSame([$logicStub, 'getMediaUrl'], $first->getCallable());
    }
}
