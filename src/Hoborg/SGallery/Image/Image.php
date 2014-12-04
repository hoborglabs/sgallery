<?php
namespace Hoborg\SGallery\Image;

class Image {
	static public function createFromConfig(array $config) {
		if ('gd' == $config['image.module']) {
			return GD::createFromConfig($config);
		}
	}
}
