<?php

use Hoborg\SGallery\Application;

class ApplicationTest extends \PHPUnit_Framework_TestCase {

	protected $fixtureClass = '\\Hoborg\\SGallery\Application';

	public function testSetWrongApplicationRoot() {
		$fixture = $this->getMock($this->fixtureClass, array('renderError'));
		$fixture->expects($this->once())
			->method('renderError');

		$fixture->setApplicationRoot('loremipsum');
	}
	
	public function testSetApplicationRootWithNoConfigDir() {
		$appRoot = TEST_ROOT . '/fixtures/appNoConfig';
		$fixture = $this->getMock($this->fixtureClass, array('renderError'));
		$fixture->expects($this->once())
			->method('renderError')
			->with($this->stringContains('Configuration folder is not readable'));

		mkdir($appRoot);
		$fixture->setApplicationRoot($appRoot);
		rmdir($appRoot);
	}

	public function testSetApplicationRootWithCorrectFolder() {
		$appRoot = TEST_ROOT . '/fixtures/appWithConfig';
		$fixture = $this->getMock($this->fixtureClass, array('renderError'));
		$fixture->expects($this->never())
			->method('renderError');

		mkdir($appRoot);
		mkdir($appRoot . '/conf');
		$fixture->setApplicationRoot($appRoot);
		rmdir($appRoot . '/conf');
		rmdir($appRoot);
		
	}

	public function testSetConfigurationOverrideWithWrongPathToFile() {
		$appRoot = TEST_ROOT . '/fixtures/appNoOverride';
		$fixture = $this->getMock($this->fixtureClass, array('renderError'));
		$fixture->expects($this->once())
			->method('renderError')
			->with($this->stringContains('Configuration override file not readable'));

		mkdir($appRoot);
		mkdir($appRoot . '/conf');
		$fixture->setConfigurationOverride('not-existing-file.ini');
		$config = $fixture->getConfiguration();
		rmdir($appRoot . '/conf');
		rmdir($appRoot);
		
	}

	public function testSetEmptyConfigurationOverride() {
		$appRoot = TEST_ROOT . '/fixtures/exampleApp';
		$fixture = $this->getMock($this->fixtureClass, array('renderError'));
		$override = TEST_ROOT . '/fixtures/override.ini';

		$fixture->setApplicationRoot($appRoot);
		file_put_contents($override, '; just a comment line');
		$fixture->setConfigurationOverride($override);
		$config = $fixture->getConfiguration();

		$this->assertEquals('not overriden', $config['override.test']);
	}

	public function testSetConfigurationOverride() {
		$appRoot = TEST_ROOT . '/fixtures/exampleApp';
		$fixture = $this->getMock($this->fixtureClass, array('renderError'));
		$override = TEST_ROOT . '/fixtures/override.ini';

		$fixture->setApplicationRoot($appRoot);

		// check value before overriding 
		$config = $fixture->getConfiguration();
		$this->assertEquals('not overriden', $config['override.test']);

		// overrides	
		file_put_contents($override, 'override.test = overriden');
		$fixture->setConfigurationOverride($override);
		$config = $fixture->getConfiguration();

		$this->assertEquals('overriden', $config['override.test']);

	}

	public function testAddExtensionPath() {
		$appRoot = TEST_ROOT . '/fixtures/exampleApp';
		$fixture = $this->getMock($this->fixtureClass, array('renderError'));
		$fixture->setApplicationRoot($appRoot);

		// before adding extension, we can find 01.json in main app data
		$actualPath = $fixture->findPath('data/01.json');
		$this->assertEquals($appRoot . '/data/01.json', $actualPath);
		
		// now let's add extension
		$extensionRoot = TEST_ROOT . '/fixtures/extension';
		mkdir($extensionRoot);
		mkdir($extensionRoot . '/data');
		file_put_contents($extensionRoot . '/data/01.json', '{"example": 1.1}');

		$fixture->addExtensionPath($extensionRoot);
		$actualPath = $fixture->findPath('data/01.json');
		$this->assertEquals($extensionRoot . '/data/01.json', $actualPath);

		unlink($extensionRoot . '/data/01.json');
		rmdir($extensionRoot . '/data');
		rmdir($extensionRoot);
	}
}
