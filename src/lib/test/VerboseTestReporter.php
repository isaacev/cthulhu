<?php

namespace Cthulhu\lib\test;

use Cthulhu\lib\fmt;

class VerboseTestReporter extends TestReporter {
  protected float $total_buildtime = 0;

  public function on_start(): void {
    $this->formatter
      ->newline_if_not_already()
      ->push_tab_stop(32);
  }

  public function on_pre_run(Test $test): void {
    parent::on_pre_run($test);
    $this->formatter
      ->print($test->group_and_name())
      ->space()
      ->apply_styles(fmt\Foreground::WHITE)
      ->tab('.')
      ->reset_styles()
      ->space();
  }

  public function on_pass(TestPassed $result): void {
    $this->total_buildtime += $result->buildtime;
    parent::on_pass($result);
    $this->formatter
      ->apply_styles(fmt\Foreground::GREEN)
      ->print('✓')
      ->space()
      ->apply_styles(fmt\Foreground::WHITE)
      ->print($this->format_time($result->buildtime))
      ->reset_styles()
      ->newline();
  }

  public function on_fail(TestFailed $result): void {
    $this->total_buildtime += $result->buildtime;
    parent::on_fail($result);
    $this->formatter
      ->apply_styles(fmt\Foreground::RED)
      ->print('✗')
      ->space()
      ->apply_styles(fmt\Foreground::WHITE)
      ->print($this->format_time($result->buildtime))
      ->reset_styles()
      ->newline();
  }

  public function on_diff(): void {
    $this->formatter
      ->pop_tab_stop();
    parent::on_diff();
  }

  public function on_stats(): void {
    parent::on_stats();
    $this->formatter
      ->printf('took    %-5s', $this->format_time($this->total_buildtime))
      ->newline();
  }

  private function format_time(float $in_milliseconds): string {
    if ($in_milliseconds < 1000) {
      return sprintf('%5.1f ms', $in_milliseconds);
    } else if ($in_milliseconds < 60 * 1000) {
      return sprintf("%5.1f sec", $in_milliseconds / 1000);
    } else {
      return sprintf("%5.1f min", $in_milliseconds / 60 / 1000);
    }
  }
}
