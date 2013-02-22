<?php
namespace Hoborg\SGallery\Command;

use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface;

class InstallAssetsCommand extends Command {

	protected $assetsFolders = array(
		'/static/scripts/hoborglabs',
		'/static/styles/hoborglabs/css',
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
		$progressOut = new \Hoborg\SGallery\Output\Progress($output);

		$progressOut->printStatus('JS', $this->copyJs($config));
		$progressOut->printStatus('CSS', $this->copyCss($config));
		$progressOut->printStatus('PHP', $this->copyPhp($config));

		$output->writeln("<info>  done.</info>");
	}

	protected function copyJs(array $config) {
		$sourceDir = $this->getApplication()->getAppRoot() . '/dist/static/scripts/hoborglabs';
		$targetDir = $config['target'] . '/static/scripts/hoborglabs';
		return copy($sourceDir . '/app.js', $targetDir . '/app.js');
	}

	protected function copyCss(array $config) {
		$sourceDir = $this->getApplication()->getAppRoot() . '/dist/static/styles/hoborglabs/css';
		$targetDir = $config['target'] . '/static/styles/hoborglabs/css';
		return copy($sourceDir . '/main.css', $targetDir . '/main.css');
	}

	protected function copyPhp(array $config) {
		$sourceDir = $this->getApplication()->getAppRoot() . '/dist/';
		$targetDir = $config['target'] . '/';
		copy($sourceDir . '/img-proxy.php', $targetDir . '/img-proxy.php');

		file_put_contents($targetDir . '/img-proxy.php',
				'function getConfig() { return array(\'source\' => \'' . addslashes($config['source']) . '\'); }'
				, FILE_APPEND);

		return true;
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
