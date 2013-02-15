<?php
namespace Hoborg\SGallery\Image;

interface Image {

	/**
	 * Returns true if class can be, for instance all extensions needed are loaded.
	 *
	 * @return bool
	 */
	public static function isEnabled();

	/**
	 * Create thumbnail for given @a $srcFile.
	 *
	 * If Image::force is set to tru, this function will regenerate thumbnail file if it already
	 * exists in @a $dstFile.
	 *
	 * @param string $srcFile
	 * @param string $dstFile
	 * @param int $size
	 *
	 * @return bool
	 */
	function makeThumbnail($srcFile, $dstFile, $size);
}
