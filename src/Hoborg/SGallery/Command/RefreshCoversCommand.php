<?php
namespace Hoborg\SGallery\Command;

use Hoborg\SGallery\Image\Finder,
	Hoborg\SGallery\Image\Image,
	Hoborg\SGallery\Image\ImageInterface;
use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface;

class RefreshCoversCommand extends Command {

	protected $imageFinder = null;

	protected $image = null;

	protected function configure() {
		$this->setName('refresh:covers')
			->setDescription('Refresh albums cover. Make sure you run refresh:thumbnails first');
	}

	public function inject(ImageInterface $image, Finder $imageFinder) {
		$this->image = $image;
		$this->imageFinder = $imageFinder;
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
		$images = array();

		// read album properties.
		if (is_file("{$folder}/sgallery.properties")) {
			$albumInfo = parse_ini_file("{$folder}/sgallery.properties");
			if (!empty($albumInfo['cover'])) {
				$thumbFileName = $this->imageFinder->getThumbnailFileName("{$folder}/{$albumInfo['cover']}", '.jpg');
				if (is_file($thumbFileName)) {
					$images[] = $thumbFileName;
				}
			}
		}

		if (empty($images)) {
			// scan folder for images
			$dir = scandir($folder);
			foreach ($dir as $entry) {
				// skip . .. and any file/folder that starts with "."
				if (0 === strpos($entry, '.')) {
					continue;
				}

				if (is_file("{$folder}/{$entry}")) {
					// look for thumbnail
					$thumbFileName = $this->imageFinder->getThumbnailFileName("{$folder}/{$entry}", '.jpg');
					if (is_file($thumbFileName)) {
						$images[] = $thumbFileName;
					}
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
					$coverFileName = $this->imageFinder->getThumbnailFileName("{$folder}/{$entry}", '-cvr.jpg');
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
		if (count($images) < $limit1tile) {
			$coverImagesCount = 1;
		} else if (count($images) < $limit2tile) {
			$coverImagesCount = 2;
		}
		for ($i = 0; $i < $coverImagesCount; $i++) {
			$rand = rand(0, count($images) - 1);
			$coverImages[] = $images[$rand];
			unset($images[$rand]);
			$images = array_values($images);
		}

		// use thumbnails
		$coverFileName = $this->imageFinder->getThumbnailFileName($folder, '-cvr.jpg');
		$coverSize = isset($config['thumbnails.size']) ? $config['thumbnails.size'] : 230;
		$this->imageFinder->ensureFodlerExists(dirname($coverFileName));
		$ret = $this->image->assembleCover($coverImages, $coverFileName, $coverSize);
		$this->imageFinder->ensureFileMode($coverFileName);

		return $ret;
	}

	protected function check(array $config) {
		if (!is_readable($config['source'])) {
			throw new \Exception('Source folder not readable', 1);
		}
		if (!is_readable($config['target'])) {
			throw new \Exception('Target folder not readable', 1);
		}

		$this->imageFinder->ensureFodlerExists($this->imageFinder->getThumbnailsFolder());
	}
}
