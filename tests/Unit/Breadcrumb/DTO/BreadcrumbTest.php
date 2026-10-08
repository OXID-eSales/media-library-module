<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Breadcrumb\DTO;

use OxidEsales\MediaLibrary\Breadcrumb\DTO\Breadcrumb;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Breadcrumb::class)]
class BreadcrumbTest extends TestCase
{
    #[Test]
    public function getters(): void
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
