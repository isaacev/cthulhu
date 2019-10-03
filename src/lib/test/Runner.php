<?php

namespace Cthulhu\lib\test;

class Runner {
  const DEFAULT_DIR = './tests';
  const VALID_TEST_EXTENSIONS = ['input', 'stderr', 'stdout'];

  public static function find_tests(string $starting_dir = self::DEFAULT_DIR): array {
    $root_path = realpath($starting_dir);
    if ($root_path === false) {
      return [];
    }

    /**
     * Since each test can have a few files associated with it, collect files
     * that look like they may be part of a test in the `$test_tuples` data
     * structure.
     *
     * The data structures has the type:
     * {
     *   directory_path => {
     *     test_name => {
     *       test_file_extension => test_file_fullpath
     *       ...
     *     }
     *     ...
     *   }
     *   ...
     * }
     */
    $test_mapping = [];
    $directory_queue = [$root_path];
    while (empty($directory_queue) === false) {
      $directory_path = array_shift($directory_queue);
      foreach (@scandir($directory_path) as $segment) {
        if ($segment === '.' || $segment === '..') {
          continue;
        }

        $child_path = "$directory_path/$segment";
        if (@is_dir($child_path)) {
          $directory_queue[] = $child_path;
        } else if (@is_file($child_path)) {
          $child_pathinfo = pathinfo($child_path);
          $child_dirname = $child_pathinfo['dirname'];
          $child_filename = $child_pathinfo['filename'];
          $child_extension = $child_pathinfo['extension'];

          if (in_array($child_extension, self::VALID_TEST_EXTENSIONS) === false) {
            continue;
          }

          $test_mapping = array_merge_recursive($test_mapping, [
            $child_dirname => [
              $child_filename => [
                $child_extension => $child_path
              ]
            ]
          ]);
        }
      }
    }

    $tests = [];
    foreach ($test_mapping as $test_dir => $tests_in_dir) {
      $test_group = str_replace($root_path . '/', '', $test_dir);
      foreach ($tests_in_dir as $test_name => $test_files) {
        $has_input = array_key_exists('input', $test_files);
        $has_stderr = array_key_exists('stderr', $test_files);
        $has_stdout = array_key_exists('stdout', $test_files);

        $input = @file_get_contents($test_files['input']);
        $stderr = $has_stderr ? file_get_contents($test_files['stderr']) : '';
        $stdout = $has_stdout ? file_get_contents($test_files['stdout']) : '';

        if ($input === false || $stderr === false || $stdout === false) {
          continue;
        }

        $expected = new TestOutput($stdout, $stderr);
        $tests[] = new Test($test_dir, $test_group, $test_name, $input, $expected);
      }
    }
    return $tests;
  }
}
