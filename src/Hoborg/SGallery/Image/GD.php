<?php
namespace Hoborg\SGallery\Image;

class GD implements Image {

	/**
	 *
	 * @var boolean
	 */
	protected $force = false;

	protected $quality = 75;

	protected $imageMaxDim = 4096;

	protected $exifEnabled = false;

	public static function isEnabled() {
		return extension_loaded('gd');
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

		// Output
		return imagejpeg($thumb, $dstFile, $this->quality);
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
}