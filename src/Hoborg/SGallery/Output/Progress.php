<?php
namespace Hoborg\SGallery\Output;

class Progress {

	protected $output = null;

	protected $progress = array(
		'index' => 0,
		'total' => 0,
	);

	public function __construct($output, $total = 0) {
		$this->output = $output;
		$this->progress['total'] = $total;
	}

	public function printProgress($success = true) {
		$this->progress['index']++;

		if ($success) {
			$this->output->write('<fg=green>.</fg=green>');
		} else {
			$this->output->write('<fg=red>E</fg=red>');
		}

		if ($this->progress['index'] == $this->progress['total']) {
			$pad = 72 - ($this->progress['total'] % 60);
			$this->output->writeln(str_pad("{$this->progress['index']}|100%", $pad, ' ', STR_PAD_LEFT));
		} else if (0 == $this->progress['index'] % 60) {
			$done = str_pad(round(100 * $this->progress['index']/$this->progress['total']).'%', 4, ' ');
			$this->output->writeln(str_pad("{$this->progress['index']}|{$done}", 12, ' ', STR_PAD_LEFT));
		}
	}

	public function printStatus($name, $success = true) {
		$this->output->write(str_pad($name, 65, ' '));
		if ($success) {
			$this->output->writeln('   <fg=green>[OK]</fg=green>');
		} else {
			$this->output->writeln('<fg=red>[ERROR]</fg=red>');
		}
	}
}