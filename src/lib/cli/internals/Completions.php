<?php

namespace Cthulhu\lib\cli\internals;

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

  /**
   * @param Scanner $scanner
   * @param Node    $start
   * @return GuardedNode[]
   */
  private static function frontier(Scanner $scanner, Node $start): array {
    $frontier = $start->find_guarded();
    while ($token = $scanner->advance()) {
      /* @var GuardedNode[] $new_frontier */
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
      fprintf(STDERR, 'unknown flag type: ' . get_class($flag));
      exit(1);
    }
  }

  private static function trace_argument(ArgumentGrammar $arg, Node $after) {
    if ($arg instanceof SingleArgumentGrammar) {
      return new PatternNode($after, '/\S/');
    } else if ($arg instanceof OptionalSingleArgumentGrammar) {
      return new Node([ $after, new PatternNode($after, '/\S/') ]);
    } else if ($arg instanceof VariadicArgumentGrammar) {
      $start             = new Node([ $after ]);
      $start->to_nodes[] = new PatternNode($start, '/\S/');
      return $start;
    } else {
      fprintf(STDERR, 'unknown argument type: ' . get_class($arg));
      exit(1);
    }
  }
}
