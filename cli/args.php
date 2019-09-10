<?php

namespace CLI;

function fatal(string $format, ...$args): void {
  fwrite(STDERR, sprintf($format, ...$args) . PHP_EOL);
  exit(1);
}

class ArgvStream {
  public $argv;
  public $len;
  public $index;

  function __construct(array $argv) {
    $this->argv = $argv;
    $this->len = count($argv);
    $this->index = 0;
  }

  public function empty(): bool {
    return $this->index >= $this->len;
  }

  public function remaining(): int {
    return $this->len - $this->index;
  }

  public function peek(): ?string {
    if ($this->empty()) {
      return null;
    }

    return $this->argv[$this->index];
  }

  public function peek_is(string $match): bool {
    if ($peek = $this->peek()) {
      return $peek === $match;
    }
    return false;
  }

  public function peek_is_one_of(string ...$matches): bool {
    if ($peek = $this->peek()) {
      foreach ($matches as $match) {
        if ($peek === $match) {
          return true;
        }
      }
    }
    return false;
  }

  public function peek_is_pattern(string $pattern): bool {
    if ($peek = $this->peek()) {
      return preg_match($pattern, $peek);
    }
    return false;
  }

  public function next(): ?string {
    if ($this->empty()) {
      return null;
    }

    return $this->argv[$this->index++];
  }
}

class ArgumentBuilder {
  public $name;

  function __construct(string $name) {
    $this->name = $name;
  }
}

class CommandBuilder {
  public $name;
  public $arguments;

  function __construct(string $name) {
    $this->name = $name;
    $this->arguments = [];
  }

  function argument(string $name): self {
    $this->arguments[] = new ArgumentBuilder($name);
    return $this;
  }

  function callback(callable $callback): Command {
    return new Command($this->name, $this->arguments, $callback);
  }
}

class Command {
  public $name;
  public $arguments;
  public $callback;

  function __construct(string $name, array $arguments, callable $callback) {
    $this->name = $name;
    $this->arguments = $arguments;
    $this->callback = $callback;
  }

  public function dispatch(ArgvStream $stream): void {
    // Parse any arguments provided to the command. If the number of required
    // arguments is more than the number of arguments present, emit an error.
    $found = [];
    $minimum_arguments = count($this->arguments);
    if ($stream->remaining() < $minimum_arguments) {
      fatal('command %s required %d arguments, found %d',
        $this->name,
        $minimum_arguments,
        $stream->remaining());
    } else {
      foreach ($this->arguments as $argument) {
        $found[] = $stream->next();
      }
    }

    call_user_func($this->callback, ...$found);
  }
}

class Parser {
  protected $commands = [];

  public function command(Command $command): self {
    if (array_key_exists($command->name, $this->commands)) {
      throw new \Exception("the '$command->name' already exists");
    }

    $this->commands[$command->name] = $command;
    return $this;
  }

  protected function print_help(): void {
    fwrite(STDERR, "USAGE:\n\n");
    fwrite(STDERR, "\tcthulhu <command> [... arguments]\n");

    if (empty($this->commands)) {
      return;
    }

    fwrite(STDERR, "\nCOMMANDS:\n\n");
    foreach ($this->commands as $cmd) {
      fwrite(STDERR, "\t$cmd->name");
      foreach ($cmd->arguments as $arg) {
        fwrite(STDERR, "\t<$arg->name>");
      }
      fwrite(STDERR, PHP_EOL);
    }
  }

  protected function get_command(string $name): ?Command {
    if (array_key_exists($name, $this->commands)) {
      return $this->commands[$name];
    }

    return null;
  }

  public function dispatch(array $argv): void {
    $stream = new ArgvStream($argv);

    // Skip executable path
    $stream->next();

    if ($stream->empty()) {
      $this->print_help();
      exit(0);
    }

    $command_name = $stream->next();
    if ($command = $this->get_command($command_name)) {
      $command->dispatch($stream);
    } else {
      fwrite(STDERR, "error: no such command: `$command_name`\n");
      exit(1);
    }
  }
}
