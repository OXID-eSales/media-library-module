<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Validator;

use OxidEsales\MediaLibrary\Media\DataType\FilePath;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use OxidEsales\MediaLibrary\Validation\Validator\FileNameValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileNameValidator::class)]
class FileNameValidatorTest extends TestCase
{
    public function testRegularFileNameDoesntThrowExceptions(): void
    {
        $filePathStub = $this->createConfiguredStub(FilePath::class, [
            'getFileName' => uniqid()
        ]);

        $sut = new FileNameValidator();
        $sut->validateFile($filePathStub);

        $this->addToAssertionCount(1);
    }

    public static function goodFileNamesDataProvider(): \Generator
    {
        yield "regular file name" => [
            'baseName' => uniqid(),
            'extension' => uniqid()
        ];
    }

    public static function badFileNamesDataProvider(): \Generator
    {
        yield "empty" => [
            'fileName' => ''
        ];

        yield "starts with dot" => [
            'fileName' => '.' . uniqid()
        ];
    }

    #[DataProvider('badFileNamesDataProvider')]
    public function testFileNameEmptyThrowsException(string $fileName): void
    {
        $filePathStub = $this->createConfiguredStub(FilePath::class, [
            'getFileName' => $fileName
        ]);

        $this->expectException(ValidationFailedException::class);

        $sut = new FileNameValidator();
        $sut->validateFile($filePathStub);
    }
}
