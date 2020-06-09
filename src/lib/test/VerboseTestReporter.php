<?php

namespace Cthulhu\lib\test;

use Cthulhu\lib\fmt;

class VerboseTestReporter extends TestReporter {
  public function on_start(): void {
    $this->formatter
      ->newline_if_not_already()
      ->push_tab_stop(32);
  }

  public function on_pass(TestPassed $result): void {
    parent::on_pass($result);
    $this->formatter
      ->print($result->test->group_and_name())
      ->space()
      ->apply_styles(fmt\Foreground::WHITE)
      ->tab('.')
      ->reset_styles()
      ->space()
      ->apply_styles(fmt\Foreground::GREEN)
      ->print('✓')
      ->space()
      ->apply_styles(fmt\Foreground::WHITE)
      ->printf('%5.1f ms', $result->runtime_in_ms)
      ->reset_styles()
      ->newline();
  }

  public function on_fail(TestFailed $result): void {
    parent::on_fail($result);
    $this->formatter
      ->print($result->test->group_and_name())
      ->space()
      ->apply_styles(fmt\Foreground::WHITE)
      ->tab('.')
      ->reset_styles()
      ->space()
      ->apply_styles(fmt\Foreground::RED)
      ->print('✗')
      ->space()
      ->apply_styles(fmt\Foreground::WHITE)
      ->printf('%5.1f ms', $result->runtime_in_ms)
      ->reset_styles()
      ->newline();
  }

  public function on_diff(): void {
    $this->formatter
      ->pop_tab_stop();
    parent::on_diff();
  }
}
