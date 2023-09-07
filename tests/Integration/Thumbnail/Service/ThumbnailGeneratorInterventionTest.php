<?php

/**
 *
 * @copyright   (c) digidesk - media solutions
 * @link            https://www.digidesk.de
 */

namespace OxidEsales\MediaLibrary\Tests\Integration\Thumbnail\Service;

use Intervention\Image\ImageManager;
use org\bovigo\vfs\vfsStream;
use OxidEsales\MediaLibrary\Tests\Integration\IntegrationTestCase;
use OxidEsales\MediaLibrary\Thumbnail\Service\ThumbnailGeneratorIntervention;

class ThumbnailGeneratorInterventionTest extends IntegrationTestCase
{


	public function setUp(): void
	{
		vfsStream::setup('vfsRoot');
		parent::setUp();
	}

	public function testGenerateThumbnail(): void
	{
		$sourcePath = vfsStream::url('vfsRoot/source.jpg');
		$thumbnailPath = vfsStream::url('vfsRoot/thumbnail.jpg');

		$imageManager = new ImageManager();
		$thumbnailGenerator = new ThumbnailGeneratorIntervention($imageManager);

		$thumbnailGenerator->generateThumbnail($sourcePath, $thumbnailPath, 185, true);
		self::assertFileExists($thumbnailPath);

		$thumbnailGenerator->generateThumbnail($sourcePath, $thumbnailPath, 300, false);
		self::assertFileExists($thumbnailPath);
	}



}