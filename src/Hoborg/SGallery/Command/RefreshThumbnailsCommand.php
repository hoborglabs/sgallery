<?php
namespace Hoborg\SGallery\Command;

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

		$this->initImageProcessor($config);

		$i = 0;
		foreach ($images as $image) {
			$success = $this->generateThumbnail($image);
			$this->progressOut->printProgress($success);
		}
		$output->writeln("\ndone.");
	}

	protected function initImageProcessor(array $config) {
		if (\Hoborg\SGallery\Image\GD::isEnabled()) {
			$config = $this->getApplication()->getConfiguration();
			$imageMaxDim = empty($config['thumbnails.sourceMaxSize']) ? 4000 : $config['thumbnails.sourceMaxSize'];
			$this->image = new \Hoborg\SGallery\Image\GD(array(
				'force' => false,
				'imageMaxDim' => $imageMaxDim
			));
		} else {
			throw new \Exception('Missing GD module');
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

	protected function generateThumbnail($image) {
		$ext = strtolower(preg_replace('/.*\.([^.]+)$/', '$1', $image));
		$config = $this->getApplication()->getConfiguration();
		$cacheThumb = $config['target'] . '/static/thumbnails/' . md5($image) . '.jpg';

		$imageQuality = !isset($config['thumbnails.quality']) ? 75 : $config['thumbnails.quality'];
		$imageThumbSize = empty($config['thumbnails.size']) ? 230 : $config['thumbnails.size'];

		return $this->image->makeThumbnail($image, $cacheThumb, $imageThumbSize);
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
