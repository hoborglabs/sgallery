<?php
namespace Hoborg\SGallery\Command;

use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface;

class RefreshHtmlCommand extends Command {

	protected function configure() {
		$this->setName('refresh:html')
			->setDescription('Refresh gallery HTML.');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$config = $this->getApplication()->getConfiguration();

		// check source and target folders
		$this->check($config);

		$output->writeln("scanning {$config['source']}");
		$folders = $this->scanSourceForFolders($config['source']);
		$this->processFolders($folders);
	}

	protected function scanSourceForFolders($folder) {
		$dir = scandir($folder);
		$folders = array(
			'name' => $folder,
			'path' => $folder,
			'meta' => '',
			'folders' => array(),
		);

		foreach ($dir as $entry) {
			// skip . .. and any file/folder that starts with "."
			if (0 === strpos($entry, '.')) {
				continue;
			}

			if (is_dir("{$folder}/{$entry}")) {
				$folders['folders'][] = $this->scanSourceForFolders("{$folder}/{$entry}");
				continue;
			}

			// get meta
		}

		return $folders;
	}

	protected function processFolders(array $folder) {
		$this->generateAlbum($folder);
		foreach ($folder['folders'] as $subFolder) {
			$this->processFolders($subFolder);
		}
	}

	protected function generateAlbum(array $folder) {
		$config = $this->getApplication()->getConfiguration();

		// get relative path for html
		$snail = str_replace($config['source'], '', $folder['name']);
		$snail = strtolower(str_replace(' ', '-', $snail));
		$snail = preg_replace('/-+/', '-', $snail);
		$snail = preg_replace('/[^a-zA-Z0-9\/\-_]/', '', $snail);
		var_dump($snail);
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
	}
}