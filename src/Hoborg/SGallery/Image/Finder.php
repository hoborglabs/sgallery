<?php
namespace Hoborg\SGallery\Image;

class Finder {

	protected $app;

	public function __construct($app) {
		$this->app = $app;
	}

	public function getThumbnailFileName($sourceFileName, $suffix) {
		$thumb = $this->getThumb($sourceFileName);

		return $this->getThumbnailsFolder() . $thumb . $suffix;
	}

	public function getThumbnailsFolder() {
		$config = $this->app->getConfiguration();
		return $config['target'] . '/static/thumbnails/';
	}

	public function getPublicThumbnailFileName($sourceFileName, $suffix) {
		$thumb = $this->getThumb($sourceFileName);

		return "/static/thumbnails/{$thumb}{$suffix}";
	}

	protected function getThumb($file) {
		$md5 = md5($file);
		$folder = substr($md5, 0, 3);
		$baseName = substr($md5, 3);

		return $folder . '/' . $baseName;
	}
}
