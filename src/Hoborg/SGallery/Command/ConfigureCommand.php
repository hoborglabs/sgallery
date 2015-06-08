<?php
namespace Hoborg\SGallery\Command;

use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface,
	Symfony\Component\Console\Question\ConfirmationQuestion,
	Symfony\Component\Console\Question\Question;

class ConfigureCommand extends Command {

	protected function configure() {
		$this->setName('configure')
			->setDescription('Configure SGallery.');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln("<info>Configuring SGallery</info>");

		$helper = $this->getHelper('question');
		$config = $this->getApplication()->getConfiguration();
		$basicConfig = $this->getBasicConfig();
		$advancedConfig = $this->getAdvancedConfig();

		foreach ($basicConfig as $key => $options) {
			if (isset($config[$key])) {
				$options['value'] = $config[$key];
			}
			$this->printConfigQuestion($options, $input, $output);
		}

		$question = new ConfirmationQuestion('Do you want to change Advanced Options? [n] ', false);
		$advanced = $helper->ask($input, $output, $question);

		if ($advanced) {
			foreach ($advancedConfig as $key => $options) {
				if (isset($config[$key])) {
					$options['value'] = $config[$key];
				}
				$this->printConfigQuestion($options, $input, $output);
			}
		}

		$this->writeConfigFile($config);
	}

	protected function printConfigQuestion(array $configEntry, InputInterface $input, OutputInterface $output) {
		$helper = $this->getHelper('question');
		$question;

		if (isset($configEntry['value'])) {
			$question = new Question($configEntry['text'] . "? [{$configEntry['value']}] ", $configEntry['value']);
		} else {
			$question = new Question($configEntry['text'] . '? ');
		}

		if (isset($configEntry['validator'])) {
			$question->setValidator($configEntry['validator']);
		}

		return $helper->ask($input, $output, $question);
	}

	protected function getBasicConfig() {
		return array(
			'source' => array(
				'text' => 'What is the path for your pictures',
				'value' => '~/Pictures',
				'validator' => array($this, 'folderValidator')
			),
			'target' => array(
				'text' => 'What is the path for your gallery',
				'value' => '/var/www/gallery',
				'validator' => array($this, 'folderValidator')
			)
		);
	}

	protected function getAdvancedConfig() {
		return array(
			'skin' => array(
				'text' => 'Which template to use',
				'value' => 'hoborglabs'
			),
			'language' => array(
				'text' => 'What is the gallery language',
				'value' => 'en'
			),
			'public.folderMode' => array(
				'text' => 'What mode shuld folders have',
				'value' => 0775
			),
			'public.fileMode' => array(
				'text' => 'What mode should files have',
				'value' => 0664
			),
			'image.module' => array(
				'text' => 'Which module to use to manipulate images',
				'value' => 'gd'
			),
			'thumbnails.quality' => array(
				'text' => 'Quality for saving images',
				'value' => 75,
				'validator' => array($this, 'numberValidator')
			),
			'thumbnails.size' => array(
				'text' => 'How big thumbnails should be (in pixels)',
				'value' => 230,
				'validator' => array($this, 'numberValidator')
			),
			'thumbnails.sourceMaxSize' => array(
				'text' => 'Maximum size of on input image',
				'value' => 4096,
				'validator' => array($this, 'numberValidator')
			),
			'covers.limit.1tile' => array(
				'text' => 'What is the maximum number images in folder for single image cover',
				'value' => 8,
				'validator' => array($this, 'numberValidator')
			),
			'covers.limit.2tile' => array(
				'text' => 'What is the maximum number images in folder for double image cover',
				'value' => 16
			),
		);
	}

	public function folderValidator($input) {
		if (!is_dir($input)) {
			throw new \Exception("not a folder");
		}

		if (!is_writable($input)) {
			throw new \Exception("{$input} is not a writable");
		}

		return realpath($input);
	}

	public function numberValidator($input) {
		if (!is_numeric($input)) {
			throw new \Exception("not a number");
		}

		return abs(round($input));
	}

	protected function writeConfigFile(array $config) {
		$content = '';
		foreach ($config as $key => $value) {
			if (!is_string($value)) {
				continue;
			}
			$content .= "{$key} = {$value}\n";
		}

		file_put_contents('sg.properties', $content);
	}
}
