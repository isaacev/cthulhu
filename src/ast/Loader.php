<?php

namespace Cthulhu\ast;

use Cthulhu\err\Error;
use Cthulhu\lib\cycle;
use Cthulhu\lib\trees\Visitor;
use Cthulhu\loc;

class Loader {
  private array $options = [];
  private array $path = [];

  public function __construct(array $options = []) {
    $this->options = $options;
    $this->path    = @$options['path'] ?? []; // TODO: The path should be determined per-source file.
  }

  /**
   * @param string $name
   * @return nodes\ShallowProgram
   * @throws Error
   */
  public function from_string(string $name): nodes\ShallowProgram {
    $name = new nodes\UpperName($name);
    $file = $this->parse(null, $name);
    return $this->load_from_first($file);
  }

  /**
   * @param loc\File $file
   * @return nodes\ShallowProgram
   * @throws Error
   */
  public function from_file(loc\File $file): nodes\ShallowProgram {
    $file = $this->parse_file($file);
    return $this->load_from_first($file);
  }

  /**
   * @param nodes\ShallowFile $first
   * @return nodes\ShallowProgram
   * @throws Error
   */
  public function load_from_first(nodes\ShallowFile $first): nodes\ShallowProgram {
    // An object to track connections across a directed graph. By updating this
    // graph with all of the dependency relationships, cycles can be detected
    // early and a topological ordering for all libraries can be built.
    $graph = new cycle\Graph($first);

    /**
     * A list of `nodes\ShallowLibrary`s that have yet to be linked. Newly
     * discovered dependencies are added to the end of the queue. Each turn of
     * the loop removes a library from the front of the queue and analyzes what
     * libraries it uses.
     */
    $queue = [ $first ];

    /**
     * To avoid repeated work, remember the AST for each library. The cache maps
     * `nodes\ShallowLibrary::name` to `nodes\ShallowLibrary` objects.
     */
    $cache = [ $first->name->value => $first ];

    // The main loop analyzes one un-analyzed library from the queue each turn.
    while ($lib = array_shift($queue)) {
      // Get the names of all the libraries imported by `$lib`
      foreach (self::find_dependencies($lib) as $dep_name => $dep_node) {
        // If the dependency name is already cached, use the cached AST.
        // Otherwise parse the named library and add the AST to both the queue
        // and the cache.

        // The `$dep_node` returned by `self::find_dependencies` could be null
        // if the library was automatically imported (like the `::Prelude`
        // library is). In these cases, there isn't a node in the source code to
        // use so create one now.
        $dep_node = $dep_node ?? new nodes\UpperName($dep_name);

        $dep = array_key_exists($dep_name, $cache)
          ? $cache[$dep_name]
          : ($cache[$dep_name] = $queue[] = self::parse($lib->file, $dep_node));

        $graph->add_edge($lib, $dep);
      }
    }

    if ([ $index, $libs ] = $graph->get_cycle()) {
      throw Errors::import_cycle($index, $libs);
    }

    return new nodes\ShallowProgram($graph->get_order());
  }

  /**
   * @param nodes\ShallowFile $root
   * @return nodes\UpperName[]
   */
  private static function find_dependencies(nodes\ShallowFile $root): array {
    $this_lib = $root->name->value;

    /**
     * All libraries and modules are automatically linked to the `::Prelude`
     * except for the Kernel module itself.
     *
     * @var string[] $links_to
     */
    if ($root->name->value !== 'Kernel') {
      $links_to = [ 'Prelude' ];
    } else {
      $links_to = [];
    }

    $nodes = [];

    Visitor::walk($root, [
      'ShallowUseItem' => function (nodes\ShallowUseItem $item) use (&$nodes, &$this_lib, &$links_to) {
        if ($item->path->is_extern) {
          $other_lib = empty($item->path->head)
            ? $item->path->tail
            : $item->path->head[0];
          assert($other_lib instanceof nodes\UpperName);
          $other_lib_name = $other_lib->value;
          if ($this_lib !== $other_lib_name) {
            $links_to[] = $other_lib_name;
            if (!array_key_exists($other_lib_name, $nodes)) {
              $nodes[$other_lib_name] = $other_lib;
            }
          }
        }
      },
    ]);

    $unique_links_to = array_unique($links_to);

    /**
     * TODO:
     * This was added to allow libraries and modules to reference themselves. It
     * would probably be better to replace this with a Rust-like `super` keyword
     * that could climb the module hierarchy to reference nearby modules.
     */
    if (in_array($root->name->value, $unique_links_to)) {
      unset($unique_links_to[array_search($root->name->value, $unique_links_to)]);
    }

    $names_to_nodes = [];
    foreach ($unique_links_to as $lib_name) {
      $lib_node                  = @$nodes[$lib_name] ?? null;
      $names_to_nodes[$lib_name] = $lib_node;
    }

    return $names_to_nodes;
  }

  /**
   * @param loc\File|null   $self
   * @param nodes\UpperName $name
   * @param string[]        $alternates
   * @return false|string
   */
  private function resolve_name(?loc\File $self, nodes\UpperName $name, array &$alternates) {
    $looking_for = "$name->value.cth";

    foreach ($this->path as $dir) {
      $filenames = scandir($dir);
      foreach ($filenames as $filename) {
        if ($filename === '.' || $filename === '..') {
          continue;
        }

        if ($filename === $looking_for) {
          return realpath("$dir/$filename");
        } else if ($self === null || $self->filepath !== "$dir/$filename") {
          $path_info = pathinfo("$dir/$filename");
          if ($path_info["extension"] === "cth") {
            $alternates[] = $path_info["filename"];
          }
        }
      }
    }
    return false;
  }

  /**
   * @param loc\File|null   $self
   * @param nodes\UpperName $name
   * @return nodes\ShallowFile
   * @throws Error
   */
  private function parse(?loc\File $self, nodes\UpperName $name): nodes\ShallowFile {
    $alternates    = [];
    $absolute_path = self::resolve_name($self, $name, $alternates);
    if ($absolute_path === false) {
      throw Errors::unknown_library($name, $this->path, $alternates);
    }

    $contents = @file_get_contents($absolute_path);
    if ($contents === false) {
      throw Errors::unable_to_read_file($absolute_path);
    }

    $file = new loc\File($absolute_path, $contents);
    return $this->parse_file($file);
  }

  /**
   * @param loc\File $file
   * @return nodes\ShallowFile
   * @throws Error
   */
  private function parse_file(loc\File $file): nodes\ShallowFile {
    $scanner        = new Scanner($file);
    $lexer          = new Lexer($scanner);
    $nester         = new Nester($lexer);
    $shallow_parser = new ShallowParser($file, $nester);
    return $shallow_parser->file();
  }
}
