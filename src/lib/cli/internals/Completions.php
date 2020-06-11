<?php

namespace Cthulhu\lib\cli\internals;

use Exception;

class Node {
  public array $to_nodes = [];

  public function __construct(array $to_nodes) {
    $this->to_nodes = $to_nodes;
  }

  public function find_guarded(): array {
    $guarded = [];
    foreach ($this->to_nodes as $node) {
      if ($node instanceof GuardedNode) {
        $guarded[] = $node;
      } else {
        $guarded = array_merge($guarded, $node->find_guarded());
      }
    }
    return $guarded;
  }
}

abstract class GuardedNode extends Node {
  public array $completions = [];

  public function __construct(Node $to_node, array $completions) {
    parent::__construct([ $to_node ]);
    $this->completions = $completions;
  }

  abstract public function matches(string $token): bool;

  public function completions(): array {
    return $this->completions;
  }
}

class LiteralNode extends GuardedNode {
  public function matches(string $token): bool {
    return in_array($token, $this->completions);
  }
}

class PatternNode extends GuardedNode {
  public string $pattern = '';

  public function __construct(Node $to_node, string $pattern) {
    parent::__construct($to_node, []);
    $this->pattern = $pattern;
  }

  public function matches(string $token): bool {
    return preg_match($this->pattern, $token);
  }
}

class Completions {
  public static function find(Scanner $scanner, ProgramGrammar $grammar): array {
    $start    = self::trace($grammar);
    $frontier = self::frontier($scanner, $start);

    $completions = [];
    foreach ($frontier as $f) {
      $completions = array_merge($completions, $f->completions());
    }
    return $completions;
  }

  private static function frontier(Scanner $scanner, Node $start): array {
    $frontier = $start->find_guarded();
    while ($token = $scanner->advance()) {
      $new_frontier = [];
      foreach ($frontier as $f) {
        if ($f->matches($token)) {
          $new_frontier = array_merge($new_frontier, $f->find_guarded());
        }
      }
      $frontier = $new_frontier;
    }
    return $frontier;
  }

  private static function trace(ProgramGrammar $prog): Node {
    $after_sc  = new Node([]);
    $before_sc = new Node([]);
    foreach ($prog->subcommand_grammars as $sc) {
      $before_sc->to_nodes[] = self::trace_subcommand($sc, $after_sc);
    }
    return self::trace_flags($prog->flags_grammar, $before_sc);
  }

  private static function trace_subcommand(SubcommandGrammar $sc, Node $after) {
    foreach (array_reverse($sc->argument_grammars) as $ar) {
      $after = self::trace_argument($ar, $after);
    }
    $after = self::trace_flags($sc->flags_grammar, $after);
    return new LiteralNode($after, [ $sc->id ]);
  }

  private static function trace_flags(FlagsGrammar $flags, Node $after) {
    $before = new Node([ $after ]);
    foreach ($flags->flags as $flag) {
      $before->to_nodes[] = self::trace_flag($flag, $before);
    }
    return $before;
  }

  private static function trace_flag(FlagGrammar $flag, Node $after) {
    if ($flag instanceof BoolFlagGrammar) {
      return new LiteralNode($after, $flag->completions());
    } else if ($flag instanceof StrFlagGrammar) {
      $arg = is_array($flag->pattern)
        ? new LiteralNode($after, $flag->pattern)
        : new PatternNode($after, '/\S/');
      return new LiteralNode($arg, $flag->completions());
    } else if ($flag instanceof ShortCircuitFlagGrammar) {
      return new LiteralNode($after, $flag->completions());
    } else {
      throw new Exception('unknown flag type: ' . get_class($flag));
    }
  }

  private static function trace_argument(ArgumentGrammar $arg, Node $after) {
    if ($arg instanceof SingleArgumentGrammar) {
      return new PatternNode($after, '/\S/');
    } else if ($arg instanceof VariadicArgumentGrammar) {
      $start             = new Node([ $after ]);
      $start->to_nodes[] = new PatternNode($start, '/\S/');
      return $start;
    } else {
      throw new Exception('unknown argument type: ' . get_class($arg));
    }
  }
}
