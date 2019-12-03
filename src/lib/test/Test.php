<?php

namespace Cthulhu\lib\test;

use Cthulhu\Errors;
use Cthulhu\lib\fmt;
use Cthulhu\Source;
use Cthulhu\workspace\ReadPhase;

class Test {
  public $dir;
  public $group;
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

  public function name_matches(string $filter): bool {
    $full_path = "$this->group/$this->name";
    return strpos($full_path, $filter) === 0;
  }

  public function run(): TestResult {
    $time_before = microtime(true);
    $found = $this->eval();
    $time_after = microtime(true);
    $runtime_in_ms = ($time_after - $time_before) * 1000;

    if ($this->expected->equals($found)) {
      return new TestPassed($this, $runtime_in_ms);
    }

    return new TestFailed($this, $found, $runtime_in_ms);
  }

  public function bless(TestOutput $blessed_output): void {
    $this->bless_extension("$this->dir/$this->name.stdout", $blessed_output->stdout);
    $this->bless_extension("$this->dir/$this->name.stderr", $blessed_output->stderr);
  }

  protected function bless_extension(string $filepath, string $contents): void {
    $realpath = realpath($filepath);
    if ($realpath !== false && empty($contents)) {
      // Handles the case:
      // - The file exists but is no longer needed for the test
      \unlink($realpath);
    } else if (!empty($contents)) {
      // Handles the cases:
      // - The file exists but the blessed contents are different
      // - The file doesn't exist yet but is now needed for the test
      $file = \fopen($filepath, 'w');
      fwrite($file, $contents);
      fclose($file);
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
