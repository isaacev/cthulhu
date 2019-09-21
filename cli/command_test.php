<?php

use \Cthulhu\lib\cli;
use \Cthulhu\lib\diff;
use \Cthulhu\lib\fmt;
use \Cthulhu\lib\test;

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
        ->print("✓")
        ->reset_styles()
        ->space()
        ->print($test->name)
        ->newline();
    } else {
      $stats['failed']++;
      $failed_results[] = $result;
      $f->apply_styles(fmt\Foreground::RED)
        ->print("✗")
        ->reset_styles()
        ->space()
        ->print($test->name)
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

  exit(empty($failed_results) ? 0 : 1);
}

function mismatch_diff(fmt\Formatter $f, string $desc, string $expected, string $found) {
  if ($expected === $found) {
    return;
  }

  $f->newline()
    ->push_tab_stop(3)
    ->tab()
    ->print($desc)
    ->spaces(2)
    ->print('( ')
    ->apply_styles(fmt\Foreground::RED)
    ->print('-found')
    ->reset_styles()
    ->print(' / ')
    ->apply_styles(fmt\Foreground::GREEN)
    ->print('+expected')
    ->reset_styles()
    ->print(' )')
    ->newline();

  $diff_lines = diff\Diff::lines($expected, $found);
  foreach ($diff_lines as $diff_line) {
    if ($diff_line instanceof diff\DeleteLine) {
      $f->tab()
        ->apply_styles(fmt\Foreground::RED)
        ->print('- ')
        ->print($diff_line->text())
        ->reset_styles()
        ->newline();
    } else if ($diff_line instanceof diff\InsertLine) {
      $f->tab()
        ->apply_styles(fmt\Foreground::GREEN)
        ->print('+ ')
        ->print($diff_line->text())
        ->reset_styles()
        ->newline();
    } else {
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
