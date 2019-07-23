<?php

require __DIR__ . '/vendor/autoload.php';

const MULTILINE = 'multi';
const SINGLELINE = 'single';

const PROMPT = [
  MULTILINE => '~ ',
  SINGLELINE => '> ',
];

$mode = SINGLELINE;
$tmp_buffer = '';
$binding = null;

while (true) {
  $line = readline(PROMPT[$mode]);
  if ($line === 'quit') {
    exit(0);
  }

  readline_add_history($line);

  if (preg_match('/^\?/', $line)) {
    $name = trim(substr($line, 1));
    if ($name === '') {
      $table = $binding ? $binding->to_table() : [];
      $max_name_length = max(array_map('strlen', array_keys($table)));
      if ($max_name_length > 0) {
        foreach ($table as $name => $type) {
          echo sprintf("%-${max_name_length}s : %s\n", $name, $type);
        }
      } else {
        echo "no symbol bindings\n";
      }
    } else {
      $resolved = $binding ? $binding->resolve($name) : null;
      if ($resolved === null) {
        echo "unknown symbol: $name\n";
      } else {
        echo "$name: $resolved\n";
      }
    }
    continue;
  }

  if (preg_match('/\\\\$/', $line)) {
    $line = substr($line, 0, -1);
    $tmp_buffer .= "$line\n";
    $mode = MULTILINE;
    continue;
  }

  if ($mode === MULTILINE) {
    $tmp_buffer .= $line;
    $binding = digest($tmp_buffer, $binding);
  } else {
    $binding = digest($line, $binding);
  }

  $tmp_buffer = '';
  $mode = SINGLELINE;
}

function digest($text, $binding) {
  try {
    $root = \Cthulhu\Parser\Parser::from_string($text)->parse();
    $context = \Cthulhu\Types\Checker::check_block($root->statements, $binding);
  } catch (\Cthulhu\Errors\SyntaxError $ex) {
    $msg = $ex->getMessage();
    echo "SYNTAX ERROR: $msg\n";
    return $binding;
  } catch (\Cthulhu\Errors\TypeError $ex) {
    $msg = $ex->getMessage();
    echo "TYPE ERROR: $msg\n";
    return $binding;
  }

  echo "$context->return_type\n";
  return $context->binding;
}
