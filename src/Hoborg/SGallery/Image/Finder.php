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

	public function ensureFodlerExists($folder) {
		$config = $this->app->getConfiguration();
		$mode = isset($config['public.folderMode']) ? $config['public.folderMode'] : '0750';
		$oldUmask = umask(0);

		if (!is_readable($folder)) {
			if (!mkdir($folder, intval($mode, 8), true)) {
				umask($oldUmask);
				throw new \Exception('Can not create ' . $folder, 1);
			}
		}

		umask($oldUmask);
	}

	public function ensureFileMode($file) {
		$config = $this->app->getConfiguration();
		$mode = isset($config['public.fileMode']) ? $config['public.fileMode'] : '0440';

		$oldUmask = umask(0);
		chmod($file, intval($mode, 8));
		umask($oldUmask);

		return true;
	}

	protected function getThumb($file) {
		$md5 = md5($file);
		$folder = substr($md5, 0, 3);
		$baseName = substr($md5, 3);

		return $folder . '/' . $baseName;
	}
}
