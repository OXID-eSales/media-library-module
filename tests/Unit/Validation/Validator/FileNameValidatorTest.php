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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileNameValidator::class)]
class FileNameValidatorTest extends TestCase
{
    #[Test]
    public function validateFile(): void
    {
        $filePathStub = $this->createConfiguredStub(FilePath::class, [
            'getFileName' => uniqid()
        ]);

        $sut = $this->getSut();
        $sut->validateFile($filePathStub);

        $this->addToAssertionCount(1);
    }

    public static function badFileNamesDataProvider(): \Generator
    {
        yield "empty" => [
            'fileName'        => '',
            'expectedMessage' => 'OE_MEDIA_LIBRARY_EXCEPTION_FILENAME_EMPTY',
        ];

        yield "starts with dot" => [
            'fileName'        => '.' . uniqid(),
            'expectedMessage' => 'OE_MEDIA_LIBRARY_EXCEPTION_FILENAME_STARTS_DOT',
        ];

        yield "contains forward slash" => [
            'fileName'        => 'subdir/foo.svg',
            'expectedMessage' => 'OE_MEDIA_LIBRARY_EXCEPTION_FILENAME_INVALID_PATH',
        ];

        yield "contains backslash" => [
            'fileName'        => 'subdir\\foo.svg',
            'expectedMessage' => 'OE_MEDIA_LIBRARY_EXCEPTION_FILENAME_INVALID_PATH',
        ];

        yield "contains traversal segment" => [
            'fileName'        => 'foo..bar.svg',
            'expectedMessage' => 'OE_MEDIA_LIBRARY_EXCEPTION_FILENAME_INVALID_PATH',
        ];

        yield "contains null byte" => [
            'fileName'        => "evil\0.svg",
            'expectedMessage' => 'OE_MEDIA_LIBRARY_EXCEPTION_FILENAME_INVALID_PATH',
        ];
    }

    #[Test]
    #[DataProvider('badFileNamesDataProvider')]
    public function rejectsBadFileNames(string $fileName, string $expectedMessage): void
    {
        $filePathStub = $this->createConfiguredStub(FilePath::class, [
            'getFileName' => $fileName,
        ]);

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage($expectedMessage);

        $sut = $this->getSut();
        $sut->validateFile($filePathStub);
    }

    protected function getSut(): FileNameValidator
    {
        return new FileNameValidator();
    }
}
