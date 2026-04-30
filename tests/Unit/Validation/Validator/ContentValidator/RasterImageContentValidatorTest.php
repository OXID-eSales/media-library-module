<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Unit\Validation\Validator\ContentValidator;

use OxidEsales\MediaLibrary\Media\DataType\FilePathInterface;
use OxidEsales\MediaLibrary\Validation\Exception\ValidationFailedException;
use OxidEsales\MediaLibrary\Validation\Format\DTO\FileFormat;
use OxidEsales\MediaLibrary\Validation\Validator\ContentValidator\RasterImageContentValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RasterImageContentValidator::class)]
class RasterImageContentValidatorTest extends TestCase
{
    /** @var list<string> */
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        $this->tempFiles = [];
    }

    #[DataProvider('rasterExtensionProvider')]
    #[Test]
    public function supportsReturnsTrueForRasterExtension(string $extension): void
    {
        $sut = $this->getSut();

        $this->assertTrue($sut->supports(new FileFormat($extension, [])));
    }

    public static function rasterExtensionProvider(): \Generator
    {
        yield 'jpg' => ['extension' => 'jpg'];
        yield 'jpeg' => ['extension' => 'jpeg'];
        yield 'gif' => ['extension' => 'gif'];
        yield 'png' => ['extension' => 'png'];
        yield 'webp' => ['extension' => 'webp'];
        yield 'avif' => ['extension' => 'avif'];
    }

    #[DataProvider('nonRasterExtensionProvider')]
    #[Test]
    public function supportsReturnsFalseForNonRasterExtension(string $extension): void
    {
        $sut = $this->getSut();

        $this->assertFalse($sut->supports(new FileFormat($extension, [])));
    }

    public static function nonRasterExtensionProvider(): \Generator
    {
        yield 'svg' => ['extension' => 'svg'];
        yield 'pdf' => ['extension' => 'pdf'];
        yield 'zip' => ['extension' => 'zip'];
        yield 'doc' => ['extension' => 'doc'];
    }

    #[Test]
    public function validatePassesForGenuinePngBytes(): void
    {
        $path = $this->createGenuinePng();

        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getPath' => $path,
            'getFileName' => basename($path),
        ]);

        $sut = $this->getSut();
        $sut->validate($filePathStub);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function validateThrowsWhenContentIsNotARecognizableImage(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'rasterTest_') . '.png';
        $this->tempFiles[] = $path;
        file_put_contents($path, '<?php phpinfo(); ?>');

        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getPath' => $path,
            'getFileName' => basename($path),
        ]);

        $sut = $this->getSut();

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('OE_MEDIA_LIBRARY_EXCEPTION_INVALID_IMAGE_CONTENT');

        $sut->validate($filePathStub);
    }

    #[Test]
    public function validateThrowsWhenPolyglotPngHasNonImageBytesAppended(): void
    {
        $path = $this->createGenuinePng();
        // Truncate to break PNG structure (genuine 8-byte signature plus garbage):
        $truncatedPath = $path . '.broken.png';
        $this->tempFiles[] = $truncatedPath;
        $bytes = (string)file_get_contents($path);
        // Keep only the PNG signature so getimagesize() rejects the file as not parseable.
        file_put_contents($truncatedPath, substr($bytes, 0, 8) . random_bytes(16));

        $filePathStub = $this->createConfiguredStub(FilePathInterface::class, [
            'getPath' => $truncatedPath,
            'getFileName' => basename($truncatedPath),
        ]);

        $sut = $this->getSut();

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('OE_MEDIA_LIBRARY_EXCEPTION_INVALID_IMAGE_CONTENT');

        $sut->validate($filePathStub);
    }

    private function createGenuinePng(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'rasterTest_') . '.png';
        $this->tempFiles[] = $path;
        $image = imagecreatetruecolor(4, 4);
        imagepng($image, $path);
        imagedestroy($image);
        return $path;
    }

    private function getSut(): RasterImageContentValidator
    {
        return new RasterImageContentValidator();
    }
}
