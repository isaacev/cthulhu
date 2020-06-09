<?php

use Cthulhu\lib\cli;
use Cthulhu\lib\fmt;
use Cthulhu\lib\test;

function command_test(cli\Lookup $flags, cli\Lookup $args) {
  $is_blessed  = $flags->get('bless');
  $is_verbose  = $flags->get('verbose');
  $do_php_eval = $flags->get('eval', false);
  $filter      = $args->get('filter');

  $replacements = [
    realpath(test\Runner::DEFAULT_DIR) => 'TEST_DIR',
    realpath(test\Runner::STDLIB_DIR) => 'STDLIB_DIR',
  ];

  $f        = new fmt\StreamFormatter(STDOUT);
  $tests    = test\Runner::find_tests();
  $reporter = $is_verbose
    ? new test\VerboseTestReporter(count($tests), $f)
    : new test\SimpleTestReporter(count($tests), $f);

  $reporter->on_start();
  foreach ($tests as $index => $test) {
    if ($filter !== null && !$test->name_matches($filter)) {
      $reporter->on_skip($test);
    } else {
      $result = $test->run($do_php_eval, $replacements);
      if ($is_blessed && $result instanceof test\TestFailed) {
        $test->bless($result->found);
      }

      if ($result instanceof test\TestPassed || $is_blessed) {
        $reporter->on_pass($result);
      } else {
        $reporter->on_fail($result);
      }
    }
  }

  $reporter->on_diff();

  $reporter->on_stats();

  exit($reporter->count_failed() ? 1 : 0);
}

