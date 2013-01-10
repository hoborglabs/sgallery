<?php
namespace Hoborg\SGallery\Command;

use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface;

class RefreshThumbnailsCommand extends Command {

	protected function configure() {
		$this->setName('refresh:thumbnails')
			->setDescription('Refresh gallery files');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$config = $this->getApplication()->getConfiguration();

		// check source and target folders
		$this->check($config);

		$output->writeln("<info>scanning {$config['source']}</info>");
		$images = $this->scanFolderForImages($config['source']);
		$imagesCount = count($images);
		$output->writeln("<info>found {$imagesCount} photos.</info>");

		foreach ($images as $image) {
			$this->generateThumbnail($image, $output);
		}
	}

	protected function scanFolderForImages($folder) {
		$dir = scandir($folder);
		$images = array();

		foreach ($dir as $entry) {
			// skip . .. and any file/folder that starts with "."
			if (0 === strpos($entry, '.')) {
				continue;
			}

			if (is_dir("{$folder}/{$entry}")) {
				$images = array_merge($images, $this->scanFolderForImages("{$folder}/{$entry}"));
				continue;
			}

			$ext = strtolower(preg_replace('/.*?\.([^.]+)$/', '$1', $entry));
			if (in_array($ext, array('jpg', 'jpeg', 'png'))) {
				$images[] = "{$folder}/{$entry}";
			}
		}

		return $images;
	}

	protected function generateThumbnail($image, $output) {
		$ext = strtolower(preg_replace('/.*\.([^.]+)$/', '$1', $image));
		$config = $this->getApplication()->getConfiguration();
		$cacheThumb = $config['target'] . '/static/thumbnails/' . md5($image) . ".{$ext}";

		// check if cache file exists
		if (!is_readable($cacheThumb)) {
			// Get new sizes
			list($width, $height) = getimagesize($image);
			$l = min($width, $height);
			$x = ($l == $width) ? 0 : round(($width - $l) / 2);
			$y = ($l == $height) ? 0 : round(($height - $l) / 2);

			if ('jpg' == $ext) {
				// Load
				$thumb = imagecreatetruecolor(200, 200);
				$source = imagecreatefromjpeg($image);

				// Resize
				imagecopyresized($thumb, $source, 0, 0, $x, $y, 200, 200, $l, $l);

				// Output
				imagejpeg($thumb, $cacheThumb);
				$output->write('.');
			}
		}
	}

	protected function check(array $config) {
		if (!is_readable($config['source'])) {
			throw new \Exception('Source folder not readable', 1);
		}
		if (!is_readable($config['target'])) {
			throw new \Exception('Target folder not readable', 1);
		}
		if (!is_readable($config['target'] . '/static/thumbnails')) {
			if (!mkdir($config['target'] . '/static/thumbnails', 0770, true)) {
				throw new \Exception('Can not create ' . $config['target'] . '/static/thumbnails', 1);
			}
		}
	}
}