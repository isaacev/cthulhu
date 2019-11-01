<?php

namespace Cthulhu\lib\test;

use \Cthulhu\Analysis;
use \Cthulhu\Codegen;
use \Cthulhu\Errors;
use \Cthulhu\Source;
use \Cthulhu\lib\fmt;
use Cthulhu\workspace\ReadPhase;

class Test {
  public $dir;
  public $name;
  public $input;
  public $expected;

  function __construct(string $dir, string $group, string $name, string $input, TestOutput $expected) {
    $this->dir = $dir;
    $this->group = $group;
    $this->name = $name;
    $this->input = $input;
    $this->expected = $expected;
  }

  public function run(): TestResult {
    $found = $this->eval();

    if ($this->expected->equals($found)) {
      return new TestPassed($this);
    }

    return new TestFailed($this, $found);
  }

  public function bless(TestOutput $blessed_output): void {
    $stdout_filename = realpath("$this->dir/$this->name.stdout");
    if ($stdout_filename !== false) {
      $stdout_file = fopen($stdout_filename, 'w');
      fwrite($stdout_file, $blessed_output->stdout);
      fclose($stdout_file);
    }

    $stderr_filename = realpath("$this->dir/$this->name.stderr");
    if ($stderr_filename !== false) {
      $stderr_file = fopen($stderr_filename, 'w');
      fwrite($stderr_file, $blessed_output->stderr);
      fclose($stderr_file);
    }
  }

  protected function eval(): TestOutput {
    try {
      $file = new Source\File($this->name, $this->input);
      $stdout = ReadPhase::from_memory($file)
        ->parse()
        ->link()
        ->resolve()
        ->check()
        ->codegen()
        ->optimize([
          'shake' => true,
        ])
        ->write();
      return new TestOutput($stdout, '');
    } catch (Errors\Error $err) {
      $stderr = new fmt\StringFormatter();
      $err->format($stderr);
      return new TestOutput('', $stderr);
    }
  }
}
