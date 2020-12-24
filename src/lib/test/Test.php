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

  /**
   * @param string[] $filters
   * @return bool
   */
  public function name_matches_one_of(array $filters): bool {
    foreach ($filters as $filter) {
      if ($this->name_matches($filter)) {
        return true;
      }
    }
    return false;
  }

  /**
   * @param bool     $do_php_eval
   * @param string[] $replacements
   * @return TestResult
   */
  public function run(bool $do_php_eval, array $replacements = []): TestResult {
    $buildtime = 0;
    $runtime   = 0;
    $found     = $this->eval($do_php_eval, $replacements, $buildtime, $runtime);

    if ($this->expected->equals($found)) {
      return new TestPassed($this, $buildtime, $runtime);
    }

    return new TestFailed($this, $found, $buildtime, $runtime);
  }

  public function bless(TestOutput $blessed_output): void {
    $dir  = $this->input->filepath->directory;
    $base = $this->input->basename();
    $this->bless_extension("$dir/$base.php", $blessed_output->php);
    $this->bless_extension("$dir/$base.stdout", $blessed_output->stdout);
    $this->bless_extension("$dir/$base.stderr", $blessed_output->stderr);
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

  /**
   * @param bool     $do_php_eval
   * @param string[] $replacements
   * @param float    $buildtime
   * @param float    $runtime
   * @return TestOutput
   */
  protected function eval(bool $do_php_eval, array $replacements, float &$buildtime, float &$runtime): TestOutput {
    $buildtime = 0;
    $runtime   = 0;
    $start     = microtime(true);

    try {
      $tree = LoadPhase::from_file($this->input)
        ->check()
        ->optimize([])
        ->codegen()
        ->optimize([]);

      $php       = $tree->write(new fmt\StringFormatter());
      $php       = self::do_replacements($php, $replacements);
      $buildtime = (microtime(true) - $start) * 1000;

      if ($do_php_eval) {
        $start   = microtime(true);
        $output  = $tree->run_and_capture();
        $stdout  = self::do_replacements($output['stdout'], $replacements);
        $stderr  = self::do_replacements($output['stderr'], $replacements);
        $runtime = (microtime(true) - $start) * 1000;
      } else {
        $stdout  = $this->expected->stdout;
        $stderr  = $this->expected->stderr;
        $runtime = 0;
      }

      return new TestOutput($php, $stdout, $stderr);
    } catch (Error $err) {
      if ($buildtime === 0) {
        $buildtime = (microtime(true) - $start) * 1000;
      }

      $stderr = new fmt\StringFormatter();
      $err->format($stderr);
      return new TestOutput('', '', self::do_replacements($stderr, $replacements));
    } catch (Exception $ex) {
      return new TestOutput('', '', "$ex");
    }
  }

  /**
   * @param string $original
   * @param array  $replacements
   * @return string
   */
  private function do_replacements(string $original, array $replacements): string {
    $replaced = $original;
    foreach ($replacements as $target => $replacement) {
      $replaced = str_replace($target, $replacement, $replaced);
    }
    return $replaced;
  }
}
