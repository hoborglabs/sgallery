<?php
namespace Hoborg\SGallery;

use Symfony\Component\Console\Application as ConsoleApplication,
	Symfony\Component\Console\Output\ConsoleOutput,
	Symfony\Component\Console\Output\ConsoleOutputInterface;
use Hoborg\SGallery\Command\RefreshCommand;

class Application extends ConsoleApplication {

	protected $appRoot = null;
	protected $configRoot = null;

	protected $configuration = null;

	public function setApplicationRoot($appRoot) {
		$this->appRoot = realpath($appRoot);
		if (empty($this->appRoot)) {
			$this->renderError("Application Root {$appRoot} is not a folder.");
		}
		if (!is_readable($this->appRoot . '/conf')) {
			$this->renderError("Configuration folder is not readable {$this->appRoot}/conf.");
		}
		$this->configRoot = $this->appRoot . '/conf';
	}

	public function getConfiguration() {
		if (is_array($this->configuration)) {
			return $this->configuration;
		}

		$this->configuration = parse_ini_file($this->configRoot . '/properties.ini', false);
		return $this->configuration;
	}

	public function renderError($error, OutputInterface $output = null) {
		if (null === $output) {
			$output = new ConsoleOutput();
		}
		$output->writeln('<info>Application Error:</info>');
		$output->writeln($error);
		exit(1);
	}

	protected function getDefaultCommands() {
		$commands = parent::getDefaultCommands();

		$commands[] = new RefreshCommand();

		return $commands;
	}

}
