<?php
namespace Hoborg\SGallery\Image;

class GD implements ImageInterface {

	/**
	 *
	 * @var boolean
	 */
	protected $force = false;

	protected $quality = 75;

	protected $imageMaxDim = 4096;

	protected $exifEnabled = false;

	public static function createFromConfig(array $config) {
		if (extension_loaded('gd')) {
			$imageMaxDim = empty($config['thumbnails.sourceMaxSize']) ? 4000 : $config['thumbnails.sourceMaxSize'];
			return new \Hoborg\SGallery\Image\GD(array(
				'force' => false,
				'imageMaxDim' => $imageMaxDim
			));
		} else {
			throw new \Exception('Missing GD module');
		}
	}

	public function __construct($options) {
		// set options
		foreach (array('force', 'quality', 'imageMaxDim') as $param) {
			if (isset($options[$param])) {
				$this->$param = $options[$param];
			}
		}

		$this->exifEnabled = extension_loaded('exif');
	}

	public function makeThumbnail($srcFile, $dstFile, $size) {
		$quality = $this->quality;

		// check if cache file exists
		if (is_readable($dstFile) && !$this->force) {
			return true;
		}

		// Get new sizes
		list($width, $height) = getimagesize($srcFile);

		if (max($width, $height) > $this->imageMaxDim) {
			return false;
		}

		$source = $this->load($srcFile);
		if (empty($source)) {
			return false;
		}

		$l = min($width, $height);
		$x = ($l == $width) ? 0 : round(($width - $l) / 2);
		$y = ($l == $height) ? 0 : round(($height - $l) / 2);

		$thumb = imagecreatetruecolor($size, $size);
		$this->ensureFodlerExists(dirname($dstFile));

		// Resize
		imagecopyresampled($thumb, $source, 0, 0, $x, $y, $size, $size, $l, $l);

		// check orientation
		if ($this->exifEnabled) {
			$exif = exif_read_data($srcFile);
			if(!empty($exif['Orientation'])) {
				switch($exif['Orientation']) {
					case 8:
						$thumb = imagerotate($thumb, 90, 0);
						break;
					case 3:
						$thumb = imagerotate($thumb, 180, 0);
						break;
					case 6:
						$thumb = imagerotate($thumb, -90, 0);
						break;
				}
			}
		}

		return imagejpeg($thumb, $dstFile, $this->quality);
	}

	public function assembleCover(array $coverImages, $coverFileName, $size) {
		$this->ensureFodlerExists(dirname($coverFileName));

		if (count($coverImages) == 1) {
			return copy($coverImages[0], $coverFileName);
		}

		$cover = imagecreatetruecolor($size, $size);
		$thumbs = array();
		foreach ($coverImages as $img) {
			$thumbs[] = imagecreatefromjpeg($img);
		}

		if (count($thumbs) == 4) {
			imagecopyresampled($cover, $thumbs[0], 0, 0, 0, 0,
					$size/2, $size/2, $size, $size);
			imagecopyresampled($cover, $thumbs[1], $size/2, 0, 0, 0,
					$size/2, $size/2, $size, $size);
			imagecopyresampled($cover, $thumbs[2], 0, $size/2, 0, 0,
					$size/2, $size/2, $size, $size);
			imagecopyresampled($cover, $thumbs[3], $size/2, $size/2, 0, 0,
					$size/2, $size/2, $size, $size);
		}
		if (count($thumbs) == 2) {
			imagecopyresampled($cover, $thumbs[0], 0, 0, 0, $size/4,
					$size, $size/2, $size, $size/2);
			imagecopyresampled($cover, $thumbs[1], 0, $size/2, 0, $size/4,
					$size, $size/2, $size, $size/2);
		}

		// Output
		return imagejpeg($cover, $coverFileName, $this->quality);
	}

	protected function load($file) {
		$ext = strtolower(preg_replace('/.*\.([^.]+)$/', '$1', $file));

		if ('jpg' == $ext || 'jpeg' == $ext) {
			return imagecreatefromjpeg($file);
		}
		if ('png' == $ext) {
			return imagecreatefrompng($file);
		}
		if ('gif' == $ext) {
			return imagecreatefromgif($file);
		}

		return null;
	}

	protected function ensureFodlerExists($folder) {
		if (!is_readable($folder)) {
			if (!mkdir($folder, 0770, true)) {
				throw new \Exception('Can not create ' . $folder, 1);
			}
		}
	}
}
