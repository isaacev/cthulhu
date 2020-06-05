<?php

namespace Cthulhu\lib\test;

use Cthulhu\err\Error;
use Cthulhu\lib\fmt;
use Cthulhu\loc\File;
use Cthulhu\workspace\LoadPhase;
use Exception;

class Test {
  public string $group;
  public File $input;
  public TestOutput $expected;

  public function __construct(string $group, File $input, TestOutput $expected) {
    $this->group    = $group;
    $this->input    = $input;
    $this->expected = $expected;
  }

  public function name(): string {
    return $this->input->basename();
  }

  public function group_and_name(): string {
    return $this->group . '/' . $this->name();
  }

  public function name_matches(string $filter): bool {
    return strpos($this->group_and_name(), $filter) === 0;
  }

  public function run(bool $do_php_eval): TestResult {
    $time_before   = microtime(true);
    $found         = $this->eval($do_php_eval);
    $time_after    = microtime(true);
    $runtime_in_ms = ($time_after - $time_before) * 1000;

    if ($this->expected->equals($found)) {
      return new TestPassed($this, $runtime_in_ms);
    }

    return new TestFailed($this, $found, $runtime_in_ms);
  }

  public function bless(TestOutput $blessed_output): void {
    $dir  = $this->input->filepath->directory;
    $base = $this->input->basename();
    $this->bless_extension("$dir/$base.php", $blessed_output->php);
    $this->bless_extension("$dir/$base.out", $blessed_output->out);
  }

  protected function bless_extension(string $filepath, string $contents): void {
    $realpath = realpath($filepath);
    if ($realpath !== false && empty($contents)) {
      // Handles the case:
      // - The file exists but is no longer needed for the test
      unlink($realpath);
    } else if (!empty($contents)) {
      // Handles the cases:
      // - The file exists but the blessed contents are different
      // - The file doesn't exist yet but is now needed for the test
      $file = fopen($filepath, 'w');
      fwrite($file, $contents);
      fclose($file);
    }
  }

  protected function eval(bool $do_php_eval): TestOutput {
    try {
      $tree = LoadPhase::from_file($this->input)
        ->check()
        ->optimize()
        ->codegen()
        ->optimize();

      $php = $tree->write();
      $out = $do_php_eval ? $tree->run() : $this->expected->out;
      return new TestOutput($php, $out);
    } catch (Error $err) {
      $out = new fmt\StringFormatter();
      $err->format($out);
      return new TestOutput('', $out);
    } catch (Exception $ex) {
      return new TestOutput('', "$ex");
    }
  }
}
