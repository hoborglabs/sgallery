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

} 
