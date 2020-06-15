<?php

namespace Cthulhu\lib\test;

use Cthulhu\loc\Directory;
use Cthulhu\loc\File;
use Cthulhu\loc\Filepath;

class Runner {
  public const DEFAULT_DIR           = './tests';
  public const STDLIB_DIR            = './src/stdlib';
  public const VALID_TEST_EXTENSIONS = [ 'cth', 'php', 'stdout', 'stderr' ];

  /**
   * @param string $starting_dir
   * @return Test[]
   */
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
    $test_mapping    = [];
    $directory_queue = [ $root_path ];
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
          $child_path_info = pathinfo($child_path);
          $child_dirname   = $child_path_info['dirname'];
          $child_filename  = $child_path_info['filename'];
          $child_extension = $child_path_info['extension'];

          if (in_array($child_extension, self::VALID_TEST_EXTENSIONS) === false) {
            continue;
          }

          $test_mapping = array_merge_recursive($test_mapping, [
            $child_dirname => [
              $child_filename => [
                $child_extension => $child_path,
              ],
            ],
          ]);
        }
      }
    }

    $tests = [];
    foreach ($test_mapping as $test_dir => $tests_in_dir) {
      $test_group = str_replace($root_path . '/', '', $test_dir);
      foreach ($tests_in_dir as $test_name => $test_files) {
        $has_cth    = array_key_exists('cth', $test_files);
        $has_php    = array_key_exists('php', $test_files);
        $has_stdout = array_key_exists('stdout', $test_files);
        $has_stderr = array_key_exists('stderr', $test_files);

        $cth    = @file_get_contents($test_files['cth']);
        $php    = $has_php ? file_get_contents($test_files['php']) : '';
        $stdout = $has_stdout ? file_get_contents($test_files['stdout']) : '';
        $stderr = $has_stderr ? file_get_contents($test_files['stderr']) : '';

        if ($cth === false || $php === false || $stdout === false || $stderr === false) {
          continue;
        }

        $expected = new TestOutput($php, $stdout, $stderr);
        $filepath = new Filepath(new Directory($test_dir, false), $test_name, 'cth');
        $file     = new File($filepath, $cth);
        $tests[]  = new Test($test_group, $file, $expected);
      }
    }
    return $tests;
  }
}
