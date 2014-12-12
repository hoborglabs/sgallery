<?php
namespace Hoborg\SGallery\Image;

interface ImageInterface {

	/**
	 * Returns instance of this class or throws an Exception if not all required extensions are loaded.
	 *
	 * @return ImageInterface
	 */
	public static function createFromConfig(array $config);

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

	/**
	 * Creates cover image.
	 *
	 * You can pass array of 1 2 or 4 images.
	 *
	 * @param array array of images to build cover from
	 * @param string
	 *
	 * @return bool
	 */
	function assembleCover(array $coverImages, $coverFileName, $size);
}
