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

class RefreshThumbnailsCommand extends Command {

	/**
	 * @var Hoborg\SGallery\Image\Image
	 */
	protected $image = null;

	protected $imageFinder = null;

	protected function configure() {
		$this->setName('refresh:thumbnails')
			->setDescription('Refresh gallery thumbnails files');
	}

	public function inject(ImageInterface $image, Finder $imageFinder) {
		$this->image = $image;
		$this->imageFinder = $imageFinder;
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
		$cacheThumb = $this->imageFinder->getThumbnailFileName($image, '.jpg');
		$imageThumbSize = empty($config['thumbnails.size']) ? 230 : $config['thumbnails.size'];

		$this->imageFinder->ensureFodlerExists(dirname($cacheThumb));
		$ret = $this->image->makeThumbnail($image, $cacheThumb, $imageThumbSize);
		$this->imageFinder->ensureFileMode($cacheThumb);

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
