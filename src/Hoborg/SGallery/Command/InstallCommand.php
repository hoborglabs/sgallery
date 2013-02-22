<?php
namespace Hoborg\SGallery\Command;

use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command {

	protected function configure() {
		$this->setName('install')
			->setDescription('Install SGallery.');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$output->writeln("<info>Installing SGallery</info>");

		$config = $this->getApplication()->getConfiguration();
		$this->check($config);

		$this->getApplication()->get('install:assets')->run($input, $output);
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
