<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Validator;

use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Settings\Service\ModuleSettingsInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use OxidEsales\MediaLibrary\Validation\Validator\FileExtensionValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FileExtensionValidatorTest extends TestCase
{
    #[DataProvider('goodFileNamesDataProvider')]
    #[Test]
    public function allowedExtensionDoesNotThrowExceptions(string $fileName): void
    {
        $sut = $this->getSut();

        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getFileName' => $fileName
        ]);

        $sut->validateFile($filePathStub);

        $this->addToAssertionCount(1);
    }

    public static function goodFileNamesDataProvider(): \Generator
    {
        yield "regular case" => [
            'fileName' => 'someImage.gif'
        ];

        yield "complex extension" => [
            'fileName' => 'someArchive.tar.gz'
        ];
    }

    #[DataProvider('wrongFileNamesDataProvider')]
    #[Test]
    public function wrongFileNameCasesThrowExceptions(string $fileName): void
    {
        $sut = $this->getSut();

        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getFileName' => $fileName
        ]);

        $this->expectException(ValidationFailedException::class);
        $sut->validateFile($filePathStub);
    }

    public static function wrongFileNamesDataProvider(): \Generator
    {
        yield "not supported extension" => [
            'fileName' => 'someImage.xxx'
        ];

        yield "no extension filename" => [
            'fileName' => 'someFileNameWithNoExtension'
        ];

        yield "allowed extension without dot" => [
            'fileName' => 'someFileNameWithDotgif'
        ];
    }

    public function getSut(): FileExtensionValidator
    {
        return new FileExtensionValidator(
            moduleSettings: $this->createConfiguredStub(ModuleSettingsInterface::class, [
                'getAllowedExtensions' => ['gif', 'tar.gz']
            ]),
        );
    }
}
