<?php
namespace Hoborg\SGallery\Output;

class ProgressTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider progressStatusAndOutputProvider
	 */
	public function testPrintProgress($status, $expectedOutput) {
		$output = $this->getMock('Output', array('write'));
		$output->expects($this->once())
			->method('write')
			->with($expectedOutput);

		$fixture = new Progress($output, 10);
		$fixture->printProgress($status);
	}

	public function progressStatusAndOutputProvider() {
		return array(
			array(
				true,
				'<fg=green>.</fg=green>'
			),
			array(
				false,
				'<fg=red>E</fg=red>'
			),
		);
	}

	public function testEndOfLine() {
		$totalItems = 70;
		$output = $this->getMock('Output', array('write', 'writeln'));
		$output->expects($this->exactly(60))
			->method('write')
			->with('<fg=green>.</fg=green>');
		$output->expects($this->once())
			->method('writeln')
			->with('     60|' . round(100 * 60/$totalItems) . '% ');
		$fixture = new Progress($output, $totalItems);

		// every 60 progress calls, new line should be printed
		for ($i = 0; $i < 60; $i++) {
			$fixture->printProgress();
		}
	}

	/**
	 * @dataProvider statusAndOutputProvider
	 */
	public function testPrintStatus($name, $success, $expectedLine, $expectedStatus) {
		$output = $this->getMock('Output', array('write', 'writeln'));
		$output->expects($this->once())
			->method('write')
			->with($expectedLine);
		$output->expects($this->once())
			->method('writeln')
			->with($expectedStatus);

		$fixture = new Progress($output, 10);
		$fixture->printStatus($name, $success);
	}

	public function statusAndOutputProvider() {
		return array(
			array(
				'test', true,
				'test                                                             ',
				'   <fg=green>[OK]</fg=green>'
			),
			array(
				'test 2', false,
				'test 2                                                           ',
				'<fg=red>[ERROR]</fg=red>'
			)
		);
	}
}
