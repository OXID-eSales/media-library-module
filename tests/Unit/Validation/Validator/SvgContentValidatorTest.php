<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Validator;

use OxidEsales\MediaLibrary\Image\Sanitizer\SvgValidatorInterface;
use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Media\DataType\UploadedFileInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use OxidEsales\MediaLibrary\Validation\Validator\SvgContentValidator;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

#[CoversClass(SvgContentValidator::class)]
class SvgContentValidatorTest extends TestCase
{
    #[DataProvider('nonSvgFileNameProvider')]
    #[Test]
    public function validateFileIgnoresNonSvgFiles(string $fileName): void
    {
        $svgValidatorSpy = $this->createMock(SvgValidatorInterface::class);
        $svgValidatorSpy->expects($this->never())->method('validate');

        $filePathStub = $this->createConfiguredStub(UploadedFileInterface::class, [
            'getFileName' => $fileName,
        ]);

        $sut = $this->getSut(svgValidator: $svgValidatorSpy);
        $sut->validateFile($filePathStub);

        $this->addToAssertionCount(1);
    }

    public static function nonSvgFileNameProvider(): \Generator
    {
        yield 'JPEG image' => ['fileName' => uniqid() . '.jpg'];
        yield 'PNG image' => ['fileName' => uniqid() . '.png'];
        yield 'PDF document' => ['fileName' => uniqid() . '.pdf'];
        yield 'archive with multi-part extension' => ['fileName' => uniqid() . '.tar.gz'];
        yield 'no extension at all' => ['fileName' => uniqid()];
    }

    #[Test]
    public function validateFileReadsContentAndDelegatesForSvg(): void
    {
        $fileName = uniqid() . '.svg';
        $fileContent = uniqid();
        $vfs = vfsStream::setup(uniqid(), null, [$fileName => $fileContent]);

        $svgValidatorSpy = $this->createMock(SvgValidatorInterface::class);
        $svgValidatorSpy->expects($this->once())
            ->method('validate')
            ->with($fileContent);

        $filePathStub = $this->createConfiguredStub(UploadedFileInterface::class, [
            'getFileName' => $fileName,
            'getPath' => $vfs->url() . '/' . $fileName,
        ]);

        $sut = $this->getSut(svgValidator: $svgValidatorSpy);
        $sut->validateFile($filePathStub);
    }

    #[Test]
    public function validateFilePropagatesValidationException(): void
    {
        $fileName = uniqid() . '.svg';
        $vfs = vfsStream::setup(uniqid(), null, [$fileName => uniqid()]);
        $exceptionMessage = uniqid();

        $svgValidatorStub = $this->createStub(SvgValidatorInterface::class);
        $svgValidatorStub->method('validate')
            ->willThrowException(new ValidationFailedException($exceptionMessage));

        $filePathStub = $this->createConfiguredStub(UploadedFileInterface::class, [
            'getFileName' => $fileName,
            'getPath' => $vfs->url() . '/' . $fileName,
        ]);

        $sut = $this->getSut(svgValidator: $svgValidatorStub);

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $sut->validateFile($filePathStub);
    }

    #[Test]
    public function validateFileIgnoresNonUploadedPaths(): void
    {
        $svgValidatorSpy = $this->createMock(SvgValidatorInterface::class);
        $svgValidatorSpy->expects($this->never())->method('validate');

        // Plain FilePathInterface, not an upload — no temp file to read.
        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getFileName' => uniqid() . '.svg',
        ]);

        $sut = $this->getSut(svgValidator: $svgValidatorSpy);
        $sut->validateFile($filePathStub);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function validateFileThrowsWhenSvgUnreadable(): void
    {
        $vfs = vfsStream::setup(uniqid());

        $svgValidatorSpy = $this->createMock(SvgValidatorInterface::class);
        $svgValidatorSpy->expects($this->never())->method('validate');

        $filePathStub = $this->createConfiguredStub(UploadedFileInterface::class, [
            'getFileName' => uniqid() . '.svg',
            'getPath' => $vfs->url() . '/' . uniqid() . '.svg',
        ]);

        $sut = $this->getSut(svgValidator: $svgValidatorSpy);

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('OE_MEDIA_LIBRARY_EXCEPTION_FILE_NOT_UPLOADED');

        $sut->validateFile($filePathStub);
    }

    #[Test]
    public function validateFileLogsRejectedSvg(): void
    {
        $fileName = uniqid() . '.svg';
        $vfs = vfsStream::setup(uniqid(), null, [$fileName => uniqid()]);

        $svgValidatorStub = $this->createStub(SvgValidatorInterface::class);
        $svgValidatorStub->method('validate')
            ->willThrowException(new ValidationFailedException(uniqid()));

        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('Rejected SVG upload'),
                $this->callback(fn(array $context) => ($context['file'] ?? null) === $fileName)
            );

        $filePathStub = $this->createConfiguredStub(UploadedFileInterface::class, [
            'getFileName' => $fileName,
            'getPath' => $vfs->url() . '/' . $fileName,
        ]);

        $sut = $this->getSut(svgValidator: $svgValidatorStub, logger: $loggerSpy);

        $this->expectException(ValidationFailedException::class);

        $sut->validateFile($filePathStub);
    }

    protected function getSut(
        ?SvgValidatorInterface $svgValidator = null,
        ?LoggerInterface $logger = null,
    ): SvgContentValidator {
        return new SvgContentValidator(
            svgValidator: $svgValidator ?? $this->createStub(SvgValidatorInterface::class),
            logger: $logger ?? new NullLogger(),
        );
    }
}
