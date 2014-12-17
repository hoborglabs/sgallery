<?php
namespace Hoborg\SGallery\Command;

use Hoborg\SGallery\Image\Finder;
use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface;

class RefreshJsonCommand extends Command {

	protected $photoExtensions = array('jpg', 'jpeg', 'png', 'gif');

	protected $progressOut = null;

	protected $imageFinder = null;

	protected function configure() {
		$this->setName('refresh:json')
			->setDescription('Refresh gallery HTML.');
	}

	public function inject(Finder $imageFinder) {
		$this->imageFinder = $imageFinder;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$output->writeln("\n<info>Refresh JSON Files.</info>");
		$config = $this->getApplication()->getConfiguration();

		// check source and target folders
		$this->check($config);
		$this->m = new \Mustache_Engine(array('charset' => 'UTF-8'));

		$this->progressOut = new \Hoborg\SGallery\Output\Progress($output, $this->countFolders($config['source']));
		$this->scanFolderForImages($config['source']);
		$output->writeln("\ndone.");
	}

	protected function scanFolderForImages($folderPath) {
		$images = array();
		$dir = scandir($folderPath);

		foreach ($dir as $entry) {
			// skip . .. and any file/folder that starts with "."
			if (0 === strpos($entry, '.')) {
				continue;
			}

			if (is_dir("{$folderPath}/{$entry}")) {
				$this->scanFolderForImages("{$folderPath}/{$entry}");
				continue;
			}

			$ext = strtolower(preg_replace('/.*?\.([^.]+)$/', '$1', $entry));
			if (in_array($ext, $this->photoExtensions)) {
				$images[] = "{$folderPath}/{$entry}";
			}
		}

		$this->generateJson($folderPath, $images);
	}

	protected function generateJson($folderPath, array $images) {
		$config = $this->getApplication()->getConfiguration();
		$tempalteRoot = $this->getApplication()->findPath('/templates/' . $config['skin']);
		$batchTemplate = $tempalteRoot . '/batch.html';
		$batchSize = 12;
		$i = 0;
		$batch = array();
		$json = array(
			'path' => $folderPath,
			'html' => '',
		);

		foreach ($images as $image) {
			$ext = strtolower(preg_replace('/.*?\.([^.]+)$/', '$1', $image));
			$batch[] = array(
				'full-size' => str_replace($config['source'], '', $image),
				'src' => $this->imageFinder->getPublicThumbnailFileName($image, ".{$ext}"),
			);

			if (count($batch) == $batchSize) {
				$jsonFileName = $config['target'] . '/static/json/' . md5($folderPath) . '-'
						. str_pad($i, 6, '0', STR_PAD_LEFT) . '.json';
				$json['html'] = $this->m->render(file_get_contents($batchTemplate), array('photos' => $batch));
				file_put_contents($jsonFileName, json_encode($json));
				$i++;
				$batch = array();
			}
		}
		// the last batch
		if (count($batch) > 0) {
			$jsonFileName = $config['target'] . '/static/json/' . md5($folderPath) . '-'
					. str_pad($i, 6, '0', STR_PAD_LEFT) . '.json';
			$json['html'] = $this->m->render(file_get_contents($batchTemplate), array('photos' => $batch));
			file_put_contents($jsonFileName, json_encode($json));
		}

		$this->progressOut->printProgress();
	}

	protected function check(array $config) {
		if (!is_readable($config['source'])) {
			throw new \Exception('Source folder not readable', 1);
		}
		if (!is_readable($config['target'])) {
			throw new \Exception('Target folder not readable', 1);
		}
		if (!is_writable($config['target'])) {
			throw new \Exception('Target folder is not writable', 1);
		}

		$this->imageFinder->ensureFodlerExists($config['target'] . '/static/json');
	}

	protected function countFolders($folder) {
		$count = 1;
		$dir = scandir($folder);

		foreach ($dir as $entry) {
			// skip . .. and any file/folder that starts with "."
			if (0 === strpos($entry, '.')) {
				continue;
			}

			if (is_dir("{$folder}/{$entry}")) {
				$count += $this->countFolders("{$folder}/{$entry}");
				continue;
			}
		}

		return $count;
	}
}
