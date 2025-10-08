<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\DataType;

use OxidEsales\MediaLibrary\Media\DataType\MediaAltText;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MediaAltText::class)]
class MediaAltTextTest extends TestCase
{
    #[Test]
    public function mediaAltText(): void
    {
        $mediaAltText = new MediaAltText(
            $objectId = uniqid(),
            $languageId = rand(1, 10),
            $text = uniqid()
        );

        $this->assertSame($objectId, $mediaAltText->getObjectId());
        $this->assertSame($languageId, $mediaAltText->getLanguageId());
        $this->assertSame($text, $mediaAltText->getText());
    }
}
