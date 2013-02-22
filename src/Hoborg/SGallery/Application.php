<?php
namespace Hoborg\SGallery;

use Symfony\Component\Console\Application as ConsoleApplication,
	Symfony\Component\Console\Output\ConsoleOutput,
	Symfony\Component\Console\Output\ConsoleOutputInterface;
use Hoborg\SGallery\Command\RefreshCommand;

class Application extends ConsoleApplication {

	protected $appRoot = null;
	protected $configRoot = null;
	protected $targetRoot = null;

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

		// i18n
		$i18nKey = empty($this->configuration['language']) ? 'en' : $this->configuration['language'];
		$i18n = parse_ini_file($this->configRoot . "/i18n/{$i18nKey}.ini", false);
		$this->configuration['i18n'] = $i18n;

		return $this->configuration;
	}

	public function getAppRoot() {
		return $this->appRoot;
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

		$commands[] = new Command\InstallCommand();
		$commands[] = new Command\InstallAssetsCommand();

		$commands[] = new Command\UpdateCommand();
		$commands[] = new Command\RefreshThumbnailsCommand();
		$commands[] = new Command\RefreshCoversCommand();
		$commands[] = new Command\RefreshHtmlCommand();
		$commands[] = new Command\RefreshJsonCommand();

		return $commands;
	}

}
