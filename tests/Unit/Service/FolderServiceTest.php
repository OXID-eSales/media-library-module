<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Service;

use OxidEsales\MediaLibrary\Service\FolderService;
use PHPUnit\Framework\TestCase;

class FolderServiceTest extends TestCase
{
    public function testCreateCustomDir(): void
    {
        $sut = new FolderService();
    }
}