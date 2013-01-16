<?php
namespace Hoborg\SGallery\Command;

use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface;

class InstallAssetsCommand extends Command {

	protected $assetsFolders = array(
		'static/scripts/hoborglabs',
		'static/styles/hoborglabs/css',
	);

	protected function configure() {
		$this->setName('install:assets')
			->setDescription('Install JS and CSS assets.');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$output->writeln("<info>Installing Assets</info>");

		$config = $this->getApplication()->getConfiguration();
		// check source and target folders
		$this->check($config);

		$this->copyJS($config);
		$this->copyCSS($config);

		$output->writeln("<info>  done.</info>");
	}

	protected function copyJS(array $config) {
		$sourceDir = $this->getApplication()->getAppRoot() . '/dist/static/scripts/hoborglabs';
		$targetDir = $config['target'] . '/static/scripts/hoborglabs';
		copy($sourceDir . '/app.js', $targetDir . 'app.js');
	}

	protected function copyCSS(array $config) {
		$sourceDir = $this->getApplication()->getAppRoot() . '/dist/static/styles/hoborglabs/css';
		$targetDir = $config['target'] . '/static/styles/hoborglabs/css';
		copy($sourceDir . '/main.css', $targetDir . '/main.css');
	}

	protected function check(array $config) {
		if (!is_readable($config['target'])) {
			throw new \Exception('Target folder not readable', 1);
		}
		if (!is_writable($config['target'])) {
			throw new \Exception('Target folder is not writable', 1);
		}

		foreach ($this->assetsFolders as $assetfolder) {
			if (!is_readable($config['target'] . $assetfolder)) {
				if (!mkdir($config['target'] . $assetfolder, 0770, true)) {
					throw new \Exception('Can not create ' . $config['target'] . $assetfolder, 1);
				}
			}
		}
	}

}
