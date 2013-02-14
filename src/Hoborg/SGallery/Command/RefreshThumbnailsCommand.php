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
			->setDescription('Refresh gallery thumbnails files');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$output->writeln("\n<info>Refresh Thumbnails Files.</info>");
		$config = $this->getApplication()->getConfiguration();

		// check source and target folders
		$this->check($config);

		$output->writeln("scanning {$config['source']} for images");
		$images = $this->scanFolderForImages($config['source']);

		// set up progress vars
		$total = count($images);
		$this->progressOut = new \Hoborg\SGallery\Output\Progress($output, $total);
		$output->writeln("found {$total} photos.");

		$i = 0;
		foreach ($images as $image) {
			$success = $this->generateThumbnail($image);
			$this->progressOut->printProgress($success);
		}
		$output->writeln("\ndone.");
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

	protected function generateThumbnail($image) {
		$ext = strtolower(preg_replace('/.*\.([^.]+)$/', '$1', $image));
		$config = $this->getApplication()->getConfiguration();
		$cacheThumb = $config['target'] . '/static/thumbnails/' . md5($image) . ".{$ext}";

		$imageQuality = isset($config['thumbnails.quality']) ? $config['thumbnails.quality'] : 75;
		$imageThumbSize = empty($config['thumbnails.size']) ? $config['thumbnails.size'] : 230;
		$imageMaxDim = empty($config['thumbnails.sourceMaxSize']) ? $config['thumbnails.sourceMaxSize'] : 4000;

		// check if cache file exists
		if (!is_readable($cacheThumb)) {
			// Get new sizes
			list($width, $height) = getimagesize($image);

			if (max($width, $height) > $imageMaxDim) {
				return false;
			}

			$l = min($width, $height);
			$x = ($l == $width) ? 0 : round(($width - $l) / 2);
			$y = ($l == $height) ? 0 : round(($height - $l) / 2);

			if ('jpg' == $ext) {
				// Load
				$thumb = imagecreatetruecolor($imageThumbSize, $imageThumbSize);
				$source = imagecreatefromjpeg($image);

				// Resize
				imagecopyresampled($thumb, $source, 0, 0, $x, $y, $imageThumbSize, $imageThumbSize, $l, $l);

				// Output
				imagejpeg($thumb, $cacheThumb, $imageQuality);
				return true;
			}
		}

		return true;
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