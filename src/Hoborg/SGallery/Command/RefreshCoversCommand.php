<?php
namespace Hoborg\SGallery\Command;

use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface;

class RefreshCoversCommand extends Command {

	protected function configure() {
		$this->setName('refresh:covers')
			->setDescription('Refresh albums cover. Make sure you run refresh:thumbnails first');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$output->writeln("\n<info>Refresh Album Covers.</info>");
		$config = $this->getApplication()->getConfiguration();

		// check source and target folders
		$this->check($config);

		$output->writeln("scanning {$config['source']} for albums");

		$albums = $this->getFolders($config['source']);
		$total = count($albums);
		$this->progressOut = new \Hoborg\SGallery\Output\Progress($output, $total);
		$output->writeln("found {$total} albums");

		// generate covers from bottom to top.
		$albums = array_reverse($albums);
		foreach ($albums as $album) {
			$success = $this->generateCover($album);
			$this->progressOut->printProgress($success);
		}
		$output->writeln("\ndone.");
	}

	protected function getFolders($folder) {
		$dir = scandir($folder);
		$folders = array(
			$folder
		);

		foreach ($dir as $entry) {
			// skip . .. and any file/folder that starts with "."
			if (0 === strpos($entry, '.')) {
				continue;
			}

			if (is_dir("{$folder}/{$entry}")) {
				$folders = array_merge($folders, $this->getFolders("{$folder}/{$entry}"));
				continue;
			}
		}

		return $folders;
	}

	protected function generateCover($folder) {

		$config = $this->getApplication()->getConfiguration();

		// scan folder for images
		$dir = scandir($folder);
		$images = array();
		foreach ($dir as $entry) {
			// skip . .. and any file/folder that starts with "."
			if (0 === strpos($entry, '.')) {
				continue;
			}

			if (is_file("{$folder}/{$entry}")) {
				// look for thumbnail
				$thumbFileName = $config['target'] . '/static/thumbnails/' . md5("{$folder}/{$entry}") . '.jpg';
				if (is_file($thumbFileName)) {
					$images[] = $thumbFileName;
				}
			}
		}

		if (empty($images)) {
			// get covers from sub-albums
			foreach ($dir as $entry) {
				// skip . .. and any file/folder that starts with "."
				if (0 === strpos($entry, '.')) {
					continue;
				}

				if (is_dir("{$folder}/{$entry}")) {
					$coverFileName = $config['target'] . '/static/thumbnails/' . md5("{$folder}/{$entry}") . '-cvr.jpg';
					if (is_file($coverFileName)) {
						$images[] = $coverFileName;
					}
				}
			}
		}

		if (empty($images)) {
			// that should never happen
			return false;
		}

		// pick 1, 2 or 4 photos
		$coverImages = array();
		$coverImagesCount = 4;
		$limit2tile = isset($config['covers.limit.2tile']) ? $config['covers.limit.2tile'] : 16;
		$limit1tile = isset($config['covers.limit.1tile']) ? $config['covers.limit.1tile'] : 8;
		if (count($images) < $limit2tile) {
			$coverImagesCount = 2;
		} else if (count($images) < $limit1tile) {
			$coverImagesCount = 1;
		}
		for ($i = 0; $i < $coverImagesCount; $i++) {
			$rand = rand(0, count($images) - 1);
			$coverImages[] = $images[$rand];
			unset($images[$rand]);
			$images = array_values($images);
		}

		// use thumbnails
		return $this->assembleCover($folder, $coverImages);
	}

	protected function assembleCover($folder, array $coverImages) {

		$config = $this->getApplication()->getConfiguration();
		$coverQuality = isset($config['thumbnails.quality']) ? $config['thumbnails.quality'] : 75;
		$coverSize = isset($config['thumbnails.size']) ? $config['thumbnails.size'] : 230;
		$coverFileName = $config['target'] . '/static/thumbnails/' . md5($folder) . '-cvr.jpg';

		if (count($coverImages) == 1) {
			return copy($coverImages[0], $coverFileName);
		}

		$cover = imagecreatetruecolor($coverSize, $coverSize);
		$thumbs = array();
		foreach ($coverImages as $img) {
			$thumbs[] = imagecreatefromjpeg($img);
		}

		if (count($thumbs) == 4) {
			imagecopyresampled($cover, $thumbs[0], 0, 0,                       0, 0,
					$coverSize/2, $coverSize/2, $coverSize, $coverSize);
			imagecopyresampled($cover, $thumbs[1], $coverSize/2, 0,            0, 0,
					$coverSize/2, $coverSize/2, $coverSize, $coverSize);
			imagecopyresampled($cover, $thumbs[2], 0, $coverSize/2,            0, 0,
					$coverSize/2, $coverSize/2, $coverSize, $coverSize);
			imagecopyresampled($cover, $thumbs[3], $coverSize/2, $coverSize/2, 0, 0,
					$coverSize/2, $coverSize/2, $coverSize, $coverSize);
		}
		if (count($thumbs) == 2) {
			imagecopyresampled($cover, $thumbs[0], 0, 0,            0, $coverSize/4,
					$coverSize, $coverSize/2, $coverSize, $coverSize/2);
			imagecopyresampled($cover, $thumbs[1], 0, $coverSize/2, 0, $coverSize/4,
					$coverSize, $coverSize/2, $coverSize, $coverSize/2);
		}

		// Output
		return imagejpeg($cover, $coverFileName, $coverQuality);
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