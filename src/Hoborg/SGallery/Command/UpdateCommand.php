<?php
namespace Hoborg\SGallery\Command;

use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command {

	protected function configure() {
		$this->setName('update')
			->setDescription('Refresh gallery files');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$config = $this->getApplication()->getConfiguration();

		// get gallery configuration
		$output->writeln('<info>Refreshing Gallery...</info>');

		$this->getApplication()->get('refresh:thumbnails')->run($input, $output);
		$this->getApplication()->get('refresh:covers')->run($input, $output);
		$this->getApplication()->get('refresh:json')->run($input, $output);
		$this->getApplication()->get('refresh:html')->run($input, $output);
		$this->getApplication()->get('install:assets')->run($input, $output);
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
}