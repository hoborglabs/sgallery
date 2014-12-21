<?php
namespace Hoborg\SGallery\Command;

use Hoborg\SGallery\Image\Finder;
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

	public function inject(Finder $imageFinder) {
		$this->imageFinder = $imageFinder;
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

		return $this->copyFile($sourceDir . '/app.js', $targetDir . '/app.js');
	}

	protected function copyCss(array $config) {
		$sourceDir = $this->getApplication()->getAppRoot() . '/dist/static/styles/hoborglabs/';
		$targetDir = $config['target'] . '/static/styles/hoborglabs/';

		// FIXME: this should be a recursive copy of folder.
		copy($sourceDir . 'gfx', $targetDir);

		return $this->copyFile($sourceDir . 'css/main.css', $targetDir . 'css/main.css');
	}

	protected function copyFile($src, $dest) {
		$this->imageFinder->ensureFodlerExists(dirname($dest));
		$ret = copy($src, $dest);
		$this->imageFinder->ensureFileMode($dest);

		return $ret;
	}

	protected function copyPhp(array $config) {
		$sourceDir = $this->getApplication()->getAppRoot() . '/dist/';
		$targetDir = $config['target'] . '/';
		$this->copyFile($sourceDir . '/img-proxy.php', $targetDir . '/img-proxy.php');

		// you can override src path for proxy
		$source = empty($config['source.proxy']) ? $config['source'] : $config['source.proxy'];
		file_put_contents($targetDir . '/img-proxy.php',
				'function getConfig() { return array(\'source\' => \'' . addslashes($source) . '\'); }'
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
			$this->imageFinder->ensureFodlerExists($config['target'] . $assetfolder);
		}
	}

}
