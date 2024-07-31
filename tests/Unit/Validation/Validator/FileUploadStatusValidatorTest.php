<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Validator;

use org\bovigo\vfs\vfsStream;
use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use OxidEsales\MediaLibrary\Validation\Validator\FileUploadStatusValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileUploadStatusValidator::class)]
class FileUploadStatusValidatorTest extends TestCase
{
    public function testValidationPassesIfFileExist(): void
    {
        $root = vfsStream::setup('root', 0777, [
            'file1.txt' => 'content1',
        ]);

        $file = $this->createConfiguredStub(FilePathInterface::class, [
            'getPath' => $root->url() . '/file1.txt',
        ]);

        $sut = new FileUploadStatusValidator();
        $sut->validateFile($file);

        $this->addToAssertionCount(1);
    }

    public function testValidationThrowsExceptionIfFileDoesNotExist(): void
    {
        $file = $this->createConfiguredStub(FilePathInterface::class, [
            'getPath' => 'notExistingFilePath',
        ]);

        $this->expectException(ValidationFailedException::class);

        $sut = new FileUploadStatusValidator();
        $sut->validateFile($file);
    }
}
