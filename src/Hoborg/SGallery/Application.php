<?php
namespace Hoborg\SGallery;

use Symfony\Component\Console\Application as ConsoleApplication,
	Symfony\Component\Console\Output\ConsoleOutput,
	Symfony\Component\Console\Output\ConsoleOutputInterface,
	Symfony\Component\Console\Output\OutputInterface,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption;
use Hoborg\SGallery\Command\RefreshCommand;

class Application extends ConsoleApplication {

	protected $appRoot = null;
	protected $configRoot = null;
	protected $targetRoot = null;
	protected $configurationOverride = null;

	protected $configuration = null;

	protected $extensions = array();

	public function init() {
		$this->getDefinition()->addOption(new InputOption(
			'--config', '-c', InputOption::VALUE_REQUIRED,
			'Specify config file.',
			'sgallery.properties'
		));

		$this->add(new Command\InstallCommand());
		$this->add(new Command\InstallAssetsCommand());

		$this->add(new Command\UpdateCommand());
		$this->add(new Command\RefreshThumbnailsCommand());
		$this->add(new Command\RefreshCoversCommand());
		$this->add(new Command\RefreshHtmlCommand());
		$this->add(new Command\RefreshJsonCommand());
	}

	public function doRun(InputInterface $input, OutputInterface $output) {
		$input->bind($this->getDefinition());
		$options = $input->getOptions();

		$this->setConfigurationOverride($options['config']);
		$this->setCatchExceptions(true);

		$imageFinder = new Image\Finder($this);
		$image = Image\Image::createFromConfig($this->getConfiguration());

		$this->get('install:assets')->inject($imageFinder);
		$this->get('refresh:thumbnails')->inject($image, $imageFinder);
		$this->get('refresh:covers')->inject($image, $imageFinder);
		$this->get('refresh:json')->inject($imageFinder);

		return parent::doRun($input, $output);
	}

	public function setApplicationRoot($appRoot) {
		$this->appRoot = $appRoot;
		if (empty($this->appRoot)) {
			return $this->renderError("Application Root {$appRoot} is not a folder.");
		}
		if (!is_readable($this->appRoot . '/conf')) {
			return $this->renderError("Configuration folder is not readable {$this->appRoot}/conf.");
		}
		$this->configRoot = $this->appRoot . '/conf';
	}

	public function setConfigurationOverride($configurationFile) {
		$this->configurationOverride = $configurationFile;
		// invalidate current configuraiton
		$this->configuration = null;
	}

	public function addExtensionPath($extensionFolder) {
		if (!is_readable($extensionFolder) || !is_dir($extensionFolder)) {
			throw new \Exception("Extension folder `{$extensionFolder}` not readable");
		}
		$this->extensions[] = $extensionFolder;
	}

	public function getConfiguration() {
		if (is_array($this->configuration)) {
			return $this->configuration;
		}

		$this->configuration = array();
		if (!empty($this->configurationOverride)) {
			if (!is_readable($this->configurationOverride)) {
				return $this->renderError("Configuration override file not readable {$this->configurationOverride}");
			}
			$this->configuration = parse_ini_file($this->configurationOverride, false);
		}
		$this->configuration += parse_ini_file($this->configRoot . '/sgallery.properties', false);

		// i18n
		$i18nKey = empty($this->configuration['language']) ? 'en' : $this->configuration['language'];
		$i18n = parse_ini_file($this->configRoot . "/i18n/{$i18nKey}.ini", false);
		$this->configuration['i18n'] = $i18n;

		return $this->configuration;
	}

	public function getAppRoot() {
		return $this->appRoot;
	}

	public function findPath($relativePath) {
		foreach ($this->extensions as $extensionRoot) {
			if (is_readable("{$extensionRoot}/{$relativePath}")) {
				return realpath("{$extensionRoot}/{$relativePath}");
			}
		}

		if (is_readable("{$this->appRoot}/{$relativePath}")) {
			return "{$this->appRoot}/{$relativePath}";
		}

		return false;
	}

	public function renderError($error, OutputInterface $output = null) {
		if (null === $output) {
			$output = new ConsoleOutput();
		}
		$output->writeln('<info>Application Error:</info>');
		$output->writeln($error);
		exit(1);
	}
}
