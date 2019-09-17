<?php

use \Cthulhu\utils\cli;
use \Cthulhu\utils\diff;
use \Cthulhu\utils\fmt;
use \Cthulhu\utils\test;

function command_test(cli\Lookup $flags, cli\Lookup $args) {
  $is_blessed = $flags->get('bless');
  $failed_results = [];
  $stats = [
    'total'  => 0,
    'passed' => 0,
    'failed' => 0,
  ];

  $f = new fmt\StreamFormatter(STDOUT);
  foreach (test\Runner::find_tests() as $test) {
    $stats['total']++;
    $result = $test->run();

    if ($is_blessed && $result instanceof test\TestFailed) {
      $test->bless($result->found);
    }

    if ($result instanceof test\TestPassed || $is_blessed) {
      $stats['passed']++;
      $f->apply_styles(fmt\Foreground::GREEN)
        ->printf("✓")
        ->reset_styles()
        ->space()
        ->printf($test->name)
        ->newline();
    } else {
      $stats['failed']++;
      $failed_results[] = $result;
      $f->apply_styles(fmt\Foreground::RED)
        ->printf("✗")
        ->reset_styles()
        ->space()
        ->printf($test->name)
        ->newline();
    }
  }

  foreach ($failed_results as $index => $result) {
    $f->newline()
      ->printf('%d) %s', $index + 1, $result->test->name)
      ->newline();

      mismatch_diff($f, 'STDOUT',
        $result->test->expected->stdout,
        $result->found->stdout);

      mismatch_diff($f, 'STDERR',
        $result->test->expected->stderr,
        $result->found->stderr);
  }

  $f->newline()
    ->printf('total  %d', $stats['total'])->newline()
    ->printf('passed %d', $stats['passed'])->newline()
    ->printf('failed %d', $stats['failed'])->newline();
}

function mismatch_diff(fmt\Formatter $f, string $desc, string $expected, string $found) {
  if ($expected === $found) {
    return;
  }

  $f->newline()
    ->push_tab_stop(3)
    ->tab()
    ->printf($desc)
    ->spaces(2)
    ->printf('( ')
    ->apply_styles(fmt\Foreground::RED)
    ->printf('-found')
    ->reset_styles()
    ->printf(' / ')
    ->apply_styles(fmt\Foreground::GREEN)
    ->printf('+expected')
    ->reset_styles()
    ->printf(' )')
    ->newline();

  $diff_lines = diff\Diff::lines($expected, $found);
  foreach ($diff_lines as $diff_line) {
    if ($diff_line instanceof diff\DeleteLine) {
      $f->tab()
        ->apply_styles(fmt\Foreground::RED)
        ->printf('- ')
        ->printf($diff_line->text())
        ->reset_styles()
        ->newline();
    } else if ($diff_line instanceof diff\InsertLine) {
      $f->tab()
        ->apply_styles(fmt\Foreground::GREEN)
        ->printf('+ ')
        ->printf($diff_line->text())
        ->reset_styles()
        ->newline();
    } else {
      $f->tab()
        ->apply_styles(fmt\Foreground::WHITE)
        ->printf('. ')
        ->printf($diff_line->text())
        ->reset_styles()
        ->newline();
    }
  }

  $f->pop_tab_stop();
}
