<?php

use Cthulhu\lib\cli;
use Cthulhu\lib\fmt;
use Cthulhu\lib\test;

function command_test(cli\Lookup $flags, cli\Lookup $args) {
  $is_blessed  = $flags->get('bless', false);
  $is_verbose  = $flags->get('verbose', false);
  $list_only   = $flags->get('list', false);
  $do_php_eval = $flags->get('eval', false);
  $filter      = $args->get('filter');
  $tests       = test\Runner::find_tests();
  $stdout      = new fmt\StreamFormatter(STDOUT);

  if ($list_only === true) {
    foreach ($tests as $index => $test) {
      if ($filter !== null && !$test->name_matches($filter)) {
        continue;
      }

      $stdout
        ->print($test->group_and_name())
        ->newline();
    }
    exit(0);
  }

  $replacements = [
    realpath(test\Runner::DEFAULT_DIR) => 'TEST_DIR',
    realpath(test\Runner::STDLIB_DIR) => 'STDLIB_DIR',
  ];

  $reporter = $is_verbose
    ? new test\VerboseTestReporter(count($tests), $stdout)
    : new test\SimpleTestReporter(count($tests), $stdout);

  $reporter->on_start();
  foreach ($tests as $index => $test) {
    if ($filter !== null && !$test->name_matches($filter)) {
      $reporter->on_skip($test);
    } else {
      $result = $test->run($do_php_eval, $replacements);
      if ($is_blessed && $result instanceof test\TestFailed) {
        $test->bless($result->found);
      }

      if ($result instanceof test\TestPassed) {
        $reporter->on_pass($result);
      } else if ($is_blessed) {
        $reporter->on_pass(new test\TestPassed($test, $result->runtime_in_ms));
      } else {
        $reporter->on_fail($result);
      }
    }
  }

  $reporter->on_diff();

  $reporter->on_stats();

  exit($reporter->count_failed() ? 1 : 0);
}

