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
use OxidEsales\MediaLibrary\Validation\Service\UploadedFileValidatorChain;
use OxidEsales\MediaLibrary\Validation\Validator\FilePathValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UploadedFileValidatorChain::class)]
class UploadedFileValidatorChainTest extends TestCase
{
    public function testConstructorDoesNotAcceptWrongType(): void
    {
        $this->expectException(ChainInputTypeException::class);
        new UploadedFileValidatorChain([new \stdClass()]);
    }

    public function testValidateFileWorksIfNoExceptionsThrown(): void
    {
        $fileStub = $this->createStub(UploadedFileInterface::class);
        $validatorStub = $this->createStub(FilePathValidatorInterface::class);

        $sut = new UploadedFileValidatorChain([$validatorStub]);
        $sut->validateFile($fileStub);

        $this->addToAssertionCount(1);
    }

    public function testExceptionOnValidatorException(): void
    {
        $fileStub = $this->createStub(UploadedFileInterface::class);

        $validatorStub = $this->createMock(FilePathValidatorInterface::class);
        $validatorStub->method('validateFile')->willThrowException(new ValidationFailedException());

        $this->expectException(ValidationFailedException::class);

        $sut = new UploadedFileValidatorChain([$validatorStub]);
        $sut->validateFile($fileStub);
    }
}
