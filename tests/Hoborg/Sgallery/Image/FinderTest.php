<?php
namespace Hoborg\SGallery\Image;

use Hoborg\SGallery\Image\Finder;

class FinderTest extends \PHPUnit_Framework_TestCase {

	protected $applicationClass = '\\Hoborg\\SGallery\\Application';

	/** @test */
	public function shouldGiveThumbnailsFileName() {
		$appMock = $this->getMock($this->applicationClass, array('renderError', 'getConfiguration'));
		$appMock->expects($this->any())
			->method('getConfiguration')
			->willReturn([ 'target' => 'test' ]);

		$finder = new Finder($appMock);
		$path = $finder->getThumbnailFileName(TEST_ROOT . '/fixtures/fake_image', '.jpg');

		$this->assertEquals('test/static/thumbnails/92d/6f3d246fa3c92d11361726f34ac83.jpg', $path);
	}

	/** @test */
	public function shouldGiveThumbnailsFlder() {
		$appMock = $this->getMock($this->applicationClass, array('renderError', 'getConfiguration'));
		$appMock->expects($this->once())
			->method('getConfiguration')
			->willReturn([ 'target' => 'test' ]);

		$finder = new Finder($appMock);
		$thumbnailsFolder = $finder->getThumbnailsFolder();

		$this->assertEquals('test/static/thumbnails/', $thumbnailsFolder);
	}

	/** @test */
	public function shouldGivePublicThumbnailPath() {
		$appMock = $this->getMock($this->applicationClass, array());
		$finder = new Finder($appMock);
		$publicThumbnail = $finder->getPublicThumbnailFileName(TEST_ROOT . '/fixtures/fake_image', '.extension');

		$this->assertEquals('/static/thumbnails/92d/6f3d246fa3c92d11361726f34ac83.extension', $publicThumbnail);
	}
}
