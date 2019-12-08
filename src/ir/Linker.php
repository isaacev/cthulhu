<?php

namespace Cthulhu\ir;

use Cthulhu\lib\cycle;
use Cthulhu\Parser;
use Cthulhu\Source;

/**
 * Given a starting library, find all other libraries that it depends on and
 * combine those libraries into a single `ir\nodes\Program` object. If an import
 * cycle is detected, emit an error and stop linking.
 */
class Linker {
  static function link(nodes\Library $first_lib): nodes\Program {
    // A object to track connections accross a directed graph. By updating this
    // graph with all of the dependency relationships, import cycles can be
    // detected early and a topological ordering for all libraries can be built.
    $graph = new cycle\Graph($first_lib);

    // A list of `nodes\Library` objects that have yet to be linked. Newly
    // discovered dependencies are added to the end of the list. Each turn of
    // the loop removes an object from the front of the list and analyzes which
    // libraries it's using.
    $queue = [ $first_lib ];

    // To avoid repeated work, remember the IR trees for each library. The cache
    // maps `nodes\Library::get_name()` strings to `nodes\Library` objects.
    $cache = [ $first_lib->get_id() => $first_lib ];

    // The main loop analyzes one un-analyzed library from the queue each turn.
    while ($lib = array_pop($queue)) {
      // Get the names of all of the libraries directly imported by the library.
      $dep_names = self::find_dependencies($lib);
      foreach ($dep_names as $dep_name) {
        // If the dependency name is already cached, use the cached IR object.
        // Otherwise, parse the named library and add the IR object to the queue
        // and to the cache.
        $dep = array_key_exists($dep_name, $cache)
          ? $cache[$dep_name]
          : ($cache[$dep_name] = $queue[] = self::parse($dep_name));
        $graph->add_edge($lib, $dep);
      }
    }

    if ([ $index, $libraries ] = $graph->get_cycle()) {
      throw Errors::import_cycle($index, ...$libraries);
    }

    return new nodes\Program($graph->get_order());
  }

  protected static function find_dependencies(nodes\Library $root): array {
    $this_lib = $root->name->value;
    // All libraries and modules are automatically linked to the Kernel (except
    // for the Kernel library itself).
    $links_to = [ 'Kernel' ];
    Visitor::walk($root, [
      'CompoundRef' => function ($ref) use (&$this_lib, &$links_to) {
        if ($ref->extern) {
          $other_lib = empty($ref->body) ? $ref->tail : $ref->body[0];
          if ($other_lib && $this_lib !== $other_lib) {
            $links_to[] = $other_lib->value;
          }
        }
      },
      'Ref' => function ($ref) use (&$this_lib, &$links_to) {
        if ($ref->extern) {
          $other_lib = empty($ref->head_segments)
            ? $ref->tail_segment
            : $ref->head_segments[0];
          if ($other_lib && $this_lib !== $other_lib) {
            $links_to[] = $other_lib->value;
          }
        }
      },
    ]);
    $unique_links_to = array_unique($links_to);

    // TODO:
    // This was added to allow libraries and modules to reference themselves. It
    // would probably be better to replace this with a Rust-like `super` keyword
    // that could climb the module hierarchy to reference nearby modules.
    if (in_array($root->name->value, $unique_links_to)) {
      unset($unique_links_to[array_search($root->name->value, $unique_links_to)]);
    }
    return $unique_links_to;
  }

  private const STDLIB_DIR = __DIR__ . '/../stdlib/';

  private static function resolve_name_in_stdlib(string $name) {
    if (!preg_match('/^[A-Z][a-zA-Z]*$/', $name)) {
      return false;
    }
    return realpath(self::STDLIB_DIR . $name . '.cth');
  }

  private static function parse(string $name): nodes\Library {
    $absolute_path = self::resolve_name_in_stdlib($name);
    if ($absolute_path === false) {
      fwrite(STDERR, sprintf("unknown standard library: `%s`\n", $name));
      exit(1);
    }

    $contents = @file_get_contents($absolute_path);
    if ($contents === false) {
      fwrite(STDERR, sprintf("cannot read file: `%s`\n", $absolute_path));
      exit(1);
    }

    $file = new Source\File($absolute_path, $contents);
    $ast  = Parser\Parser::file_to_ast($file);
    $lib  = Lower::file($ast);
    return $lib;
  }
}
