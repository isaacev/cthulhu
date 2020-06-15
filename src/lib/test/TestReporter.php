<?php

namespace Cthulhu\lib\test;

use Cthulhu\lib\diff;
use Cthulhu\lib\fmt;

abstract class TestReporter {
  protected int $total;
  protected fmt\Formatter $formatter;
  protected int $passed = 0;
  protected int $failed = 0;
  protected int $skipped = 0;

  /* @var TestFailed[] */
  protected array $failed_results = [];

  public function __construct(int $total, fmt\Formatter $formatter) {
    $this->total     = $total;
    $this->formatter = $formatter;
  }

  public function count_failed(): int {
    return $this->failed;
  }

  abstract public function on_start(): void;

  public function on_skip(Test $test): void {
    $this->skipped++;
  }

  public function on_pass(TestPassed $result): void {
    $this->passed++;
  }

  public function on_fail(TestFailed $result): void {
    $this->failed++;
    $this->failed_results[] = $result;
  }

  public function on_diff(): void {
    foreach ($this->failed_results as $index => $result) {
      $this->formatter
        ->newline()
        ->printf('%d) %s', $index + 1, $result->test->group_and_name())
        ->newline();

      self::mismatch_diff($this->formatter, 'PHP',
        $result->test->expected->php,
        $result->found->php);

      self::mismatch_diff($this->formatter, 'STDOUT',
        $result->test->expected->stdout,
        $result->found->stdout);

      self::mismatch_diff($this->formatter, 'STDERR',
        $result->test->expected->stderr,
        $result->found->stderr);
    }
  }

  public function on_stats(): void {
    $this->formatter
      ->newline()
      ->printf("passed  %d", $this->passed)
      ->newline()
      ->printf("failed  %d", $this->failed)
      ->newline()
      ->printf("skipped %d", $this->skipped)
      ->newline();
  }

  public static function mismatch_diff(fmt\Formatter $f, string $desc, string $expected, string $found) {
    if ($expected === $found) {
      return;
    }

    $f->newline()
      ->push_tab_stop(3)
      ->tab()
      ->print($desc)
      ->spaces(2)
      ->print('( ')
      ->apply_styles(fmt\Foreground::GREEN)
      ->print('-expected')
      ->reset_styles()
      ->print(' / ')
      ->apply_styles(fmt\Foreground::RED)
      ->print('+found')
      ->reset_styles()
      ->print(' )')
      ->newline();

    $diff_lines = diff\Diff::lines($expected, $found);
    foreach ($diff_lines as $diff_line) {
      if ($diff_line instanceof diff\DeleteLine) {
        $f->tab()
          ->apply_styles(fmt\Foreground::GREEN)
          ->print('- ')
          ->print($diff_line->text())
          ->reset_styles()
          ->newline();
      } else if ($diff_line instanceof diff\InsertLine) {
        $f->tab()
          ->apply_styles(fmt\Foreground::RED)
          ->print('+ ')
          ->print($diff_line->text())
          ->reset_styles()
          ->newline();
      } else if ($diff_line instanceof diff\KeepLine) {
        $f->tab()
          ->apply_styles(fmt\Foreground::WHITE)
          ->print('. ')
          ->print($diff_line->text())
          ->reset_styles()
          ->newline();
      }
    }

    $f->pop_tab_stop();
  }
}
