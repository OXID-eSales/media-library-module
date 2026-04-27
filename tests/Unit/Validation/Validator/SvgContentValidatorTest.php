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
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

#[CoversClass(SvgContentValidator::class)]
class SvgContentValidatorTest extends TestCase
{
    public function testIgnoresFilesWhoseNameIsNotSvg(): void
    {
        $svgValidator = $this->createMock(SvgValidatorInterface::class);
        $svgValidator->expects($this->never())->method('validate');

        $filePath = $this->createMock(UploadedFileInterface::class);
        $filePath->method('getFileName')->willReturn('photo.jpg');

        (new SvgContentValidator($svgValidator, new NullLogger()))->validateFile($filePath);

        $this->addToAssertionCount(1);
    }

    public function testReadsContentAndDelegatesForSvgUpload(): void
    {
        $vfs = vfsStream::setup('uploads', null, ['some.svg' => '<svg/>']);

        $svgValidator = $this->createMock(SvgValidatorInterface::class);
        $svgValidator->expects($this->once())
            ->method('validate')
            ->with('<svg/>');

        $filePath = $this->createMock(UploadedFileInterface::class);
        $filePath->method('getFileName')->willReturn('some.svg');
        $filePath->method('getPath')->willReturn($vfs->url() . '/some.svg');

        (new SvgContentValidator($svgValidator, new NullLogger()))->validateFile($filePath);
    }

    public function testPropagatesValidationException(): void
    {
        $vfs = vfsStream::setup('uploads', null, ['xss.svg' => '<svg><script/></svg>']);

        $svgValidator = $this->createMock(SvgValidatorInterface::class);
        $svgValidator->method('validate')
            ->willThrowException(new ValidationFailedException(
                'OE_MEDIA_LIBRARY_EXCEPTION_SVG_DISALLOWED_CONTENT'
            ));

        $filePath = $this->createMock(UploadedFileInterface::class);
        $filePath->method('getFileName')->willReturn('xss.svg');
        $filePath->method('getPath')->willReturn($vfs->url() . '/xss.svg');

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('OE_MEDIA_LIBRARY_EXCEPTION_SVG_DISALLOWED_CONTENT');

        (new SvgContentValidator($svgValidator, new NullLogger()))->validateFile($filePath);
    }

    public function testIgnoresNonUploadedFilePaths(): void
    {
        $svgValidator = $this->createMock(SvgValidatorInterface::class);
        $svgValidator->expects($this->never())->method('validate');

        // Plain FilePathInterface, not an upload — no temp file to read.
        $filePath = $this->createMock(FilePathInterface::class);
        $filePath->method('getFileName')->willReturn('renamed.svg');

        (new SvgContentValidator($svgValidator, new NullLogger()))->validateFile($filePath);

        $this->addToAssertionCount(1);
    }

    public function testThrowsWhenSvgFileCannotBeRead(): void
    {
        $vfs = vfsStream::setup('uploads');

        $svgValidator = $this->createMock(SvgValidatorInterface::class);
        $svgValidator->expects($this->never())->method('validate');

        $filePath = $this->createMock(UploadedFileInterface::class);
        $filePath->method('getFileName')->willReturn('missing.svg');
        $filePath->method('getPath')->willReturn($vfs->url() . '/does-not-exist.svg');

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('OE_MEDIA_LIBRARY_EXCEPTION_FILE_NOT_UPLOADED');

        (new SvgContentValidator($svgValidator, new NullLogger()))->validateFile($filePath);
    }

    public function testLogsRejectedSvgWithFileName(): void
    {
        $vfs = vfsStream::setup('uploads', null, ['xss.svg' => '<svg><script/></svg>']);

        $svgValidator = $this->createMock(SvgValidatorInterface::class);
        $svgValidator->method('validate')
            ->willThrowException(new ValidationFailedException(
                'OE_MEDIA_LIBRARY_EXCEPTION_SVG_DISALLOWED_CONTENT'
            ));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('Rejected SVG upload'),
                $this->callback(fn(array $context) => ($context['file'] ?? null) === 'xss.svg')
            );

        $filePath = $this->createMock(UploadedFileInterface::class);
        $filePath->method('getFileName')->willReturn('xss.svg');
        $filePath->method('getPath')->willReturn($vfs->url() . '/xss.svg');

        $this->expectException(ValidationFailedException::class);

        (new SvgContentValidator($svgValidator, $logger))->validateFile($filePath);
    }
}
