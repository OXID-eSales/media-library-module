<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Breadcrumb\DataType;

use OxidEsales\MediaLibrary\Breadcrumb\DataType\Breadcrumb;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Breadcrumb::class)]
class BreadcrumbTest extends TestCase
{
    public function testGetters(): void
    {
        $name = uniqid();

        $sut = new Breadcrumb(
            name: $name,
            active: true
        );

        $this->assertSame($name, $sut->getName());
        $this->assertSame(true, $sut->isActive());
    }
}
