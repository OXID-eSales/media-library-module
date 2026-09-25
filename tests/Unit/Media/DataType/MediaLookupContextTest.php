<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Media\DataType;

use OxidEsales\MediaLibrary\Media\DataType\MediaLookupContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MediaLookupContext::class)]
class MediaLookupContextTest extends TestCase
{
    #[Test]
    public function mediaLookupContext(): void
    {
        $mediaLookupContext = new MediaLookupContext(
            $trigger = uniqid(),
            $identifier = uniqid(),
            $note = uniqid()
        );

        $this->assertSame($trigger, $mediaLookupContext->getTrigger());
        $this->assertSame($identifier, $mediaLookupContext->getIdentifier());
        $this->assertSame($note, $mediaLookupContext->getNote());
    }

    #[Test]
    public function identifierAndNoteDefaultToEmptyString(): void
    {
        $mediaLookupContext = new MediaLookupContext($trigger = uniqid());

        $this->assertSame($trigger, $mediaLookupContext->getTrigger());
        $this->assertSame('', $mediaLookupContext->getIdentifier());
        $this->assertSame('', $mediaLookupContext->getNote());
    }
}
