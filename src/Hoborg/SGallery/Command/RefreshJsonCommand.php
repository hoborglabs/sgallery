<?php
namespace Hoborg\SGallery\Command;

use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface;

class RefreshJsonCommand extends Command {

	protected $photoExtensions = array('jpg', 'jpeg', 'png', 'gif');

	protected function configure() {
		$this->setName('refresh:json')
			->setDescription('Refresh gallery HTML.');

		$this->m = new \Mustache_Engine(array('charset' => 'UTF-8'));
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$config = $this->getApplication()->getConfiguration();

		// check source and target folders
		$this->check($config);

		$output->writeln("<info>Refresh JSON Files.</info>");
		$this->scanFolderForImages($config['source']);
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
		$batchTemplate = $this->getApplication()->getAppRoot() . '/templates/' . $config['skin'] . '/batch.html';
		$batchSize = 12;
		$i = 0;
		$batch = array();
		$json = array(
			'path' => $folderPath,
			'html' => '',
		);
		foreach ($images as $image) {
			$ext = strtolower(preg_replace('/.*?\.([^.]+)$/', '$1', $image));
			$cacheFile = md5($image) . ".{$ext}";
			$batch[] = array(
				'href' => '#',
				'src' => "/static/thumbnails/{$cacheFile}",
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
		if (count($batch) > 0) {
			$jsonFileName = $config['target'] . '/static/json/' . md5($folderPath) . '-'
			. str_pad($i, 6, '0', STR_PAD_LEFT) . '.json';
			$json['html'] = $this->m->render(file_get_contents($batchTemplate), array('photos' => $batch));
			file_put_contents($jsonFileName, json_encode($json));
		}
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
		if (!is_readable($config['target'] . '/static/json')) {
			if (!mkdir($config['target'] . '/static/json', 0770, true)) {
				throw new \Exception('Can not create ' . $config['target'] . '/static/json', 1);
			}
		}
	}
}