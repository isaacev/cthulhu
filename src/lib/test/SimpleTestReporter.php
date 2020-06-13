<?php

namespace Cthulhu\lib\test;

use Cthulhu\lib\fmt;

class SimpleTestReporter extends TestReporter {
  protected const MAX_ON_LINE = 50;
  protected int $on_this_line = 0;
  protected int $total_so_far = 0;
  protected string $progress_format;

  public function __construct(int $total, fmt\Formatter $formatter) {
    parent::__construct($total, $formatter);
    $this->progress_format = "%" . strlen("$total") . "d/%d";
  }

  public function on_start(): void {
    $this->formatter
      ->newline_if_not_already()
      ->printf("running %d tests", $this->total)
      ->newline();
  }

  public function on_skip(Test $test): void {
    parent::on_skip($test);
    $this->formatter
      ->apply_styles(fmt\Foreground::WHITE)
      ->print('.')
      ->reset_styles();
    $this->is_line_finished();
  }

  public function on_pass(TestPassed $result): void {
    parent::on_pass($result);
    $this->formatter
      ->apply_styles(fmt\Foreground::GREEN)
      ->print('+')
      ->reset_styles();
    $this->is_line_finished();
  }

  public function on_fail(TestFailed $result): void {
    parent::on_fail($result);
    $this->formatter
      ->apply_styles(fmt\Foreground::RED)
      ->print('x')
      ->reset_styles();
    $this->is_line_finished();
  }

  private function is_line_finished(): void {
    $this->on_this_line++;
    $this->total_so_far++;
    if ($this->on_this_line >= self::MAX_ON_LINE || $this->total_so_far >= $this->total) {
      $this->on_this_line = 0;
      $this->formatter
        ->tab_to(self::MAX_ON_LINE + 1)
        ->printf($this->progress_format, $this->total_so_far, $this->total)
        ->newline();
    }
  }
}
