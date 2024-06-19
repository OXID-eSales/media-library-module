<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Service;

use OxidEsales\MediaLibrary\Media\DataType\UploadedFileInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ChainInputTypeException;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use OxidEsales\MediaLibrary\Validation\Service\FileNameValidatorChain;
use OxidEsales\MediaLibrary\Validation\Service\UploadedFileValidatorChain;
use OxidEsales\MediaLibrary\Validation\Validator\FilePathValidatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\MediaLibrary\Validation\Service\FileNameValidatorChain
 */
class FileNameValidatorChainTest extends TestCase
{
    public function testConstructorDoesNotAcceptWrongType(): void
    {
        $this->expectException(ChainInputTypeException::class);
        new FileNameValidatorChain([new \stdClass()]);
    }

    public function testValidateFileWorksIfNoExceptionsThrown(): void
    {
        $validatorStub = $this->createStub(FilePathValidatorInterface::class);

        $sut = new FileNameValidatorChain([$validatorStub]);
        $sut->validateFileName(uniqid());

        $this->addToAssertionCount(1);
    }

    public function testExceptionOnValidatorException(): void
    {
        $fileName = uniqid();

        $validatorMock = $this->createMock(FilePathValidatorInterface::class);
        $validatorMock->method('validateFile')->willThrowException(new ValidationFailedException());

        $this->expectException(ValidationFailedException::class);

        $sut = new FileNameValidatorChain([$validatorMock]);
        $sut->validateFileName($fileName);
    }
}
