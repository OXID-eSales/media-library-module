<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Compatibility\DTO;

use OxidEsales\MediaLibrary\Compatibility\DTO\MediaFileInformation;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MediaFileInformationTest extends TestCase
{
    #[Test]
    public function gettersReturnCorrectData(): void
    {
        $fileName = uniqid();
        $folderName = uniqid();

        $sut = new MediaFileInformation(
            fileName: $fileName,
            folderName: $folderName,
        );

        $this->assertSame($fileName, $sut->getFileName());
        $this->assertSame($folderName, $sut->getFolderName());
    }
}
