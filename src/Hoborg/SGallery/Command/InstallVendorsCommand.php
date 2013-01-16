<?php
namespace Hoborg\SGallery\Command;

use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface;

class InstallVendorsCommand extends Command {

	protected $vendorsUrl = 'http://get.hoborglabs.com/sgallery/vendors';
	protected function configure() {
		$this->setName('install:vendors')
			->setDescription('Install JS and CSS assets.');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$output->writeln("<info>Installing Vendors</info>");

		// get & check config
		$config = $this->getApplication()->getConfiguration();
		$this->check($config);

		// ask user for installation type
		$dialog = $this->getHelperSet()->get('dialog');
		$type = $dialog->askAndValidate(
			$output,
			"<question>How do you want to install vendors?</question>
d|download  - download from get.hoborglabs.com
c|composer  - use composer to download vendors\n  [d]: ",
			function($type) {
				$type = strtolower($type);
				$typeMap = array(
					'd' => 'download',
					'download' => 'download',
					'c' => 'composer',
					'composer' => 'composer'
				);
				if (!isset($typeMap[$type])) {
					throw new \Exception('Please specify correct installation type.');
				}

				return $typeMap[$type];
			},
			false,
			'd'
		);

		if ($type == 'composer') {
			$this->runComposer($output);
		} else if ($type == 'download') {
			$this->downloadVendors($output);
		}

		$output->writeln("<info>  done.</info>");
	}

	protected function downloadVendors(OutputInterface $output) {
		$output->writeln("Downloading get.hoborglabs.com/sgallery/vendors.zip");

		$newfname = $this->getApplication->getAppRoot() . '/vendors.zip';
		$file = fopen ($this->vendorsUrl, "rb");
		if ($file) {
			$newf = fopen ($newfname, "wb");

			if ($newf) {
				while (!feof($file)) {
					fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
				}
			}
		}

		if ($file) {
			fclose($file);
		}

		if ($newf) {
			fclose($newf);
		}
	}

	protected function check(array $config) {
		if (!is_readable($config['target'])) {
			throw new \Exception('Target folder not readable', 1);
		}
		if (!is_writable($config['target'])) {
			throw new \Exception('Target folder is not writable', 1);
		}
	}

}
