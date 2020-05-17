<?php

namespace Cthulhu\ir\names;

class Scope {
  protected Space $modules;
  protected Space $terms;

  public function __construct() {
    $this->modules = new Space();
    $this->terms   = new Space();
  }

  /** @noinspection PhpIncompatibleReturnTypeInspection */
  public function get_module_binding(string $name): ?ModuleBinding {
    return $this->modules->get_name($name);
  }

  /** @noinspection PhpIncompatibleReturnTypeInspection */
  public function get_public_module_binding(string $name): ?ModuleBinding {
    return $this->modules->get_public_name($name);
  }

  public function add_module_binding(ModuleBinding $binding): void {
    $this->modules->add_binding($binding);
  }

  /**
   * @return string[]
   */
  public function all_public_module_names(): array {
    return $this->modules->get_public_names();
  }

  /**
   * @return ModuleBinding[]
   */
  public function all_public_module_bindings(): array {
    return $this->modules->get_public_bindings();
  }

  /**
   * @return string[]
   */
  public function all_public_and_private_module_names(): array {
    return $this->modules->get_any_names();
  }

  public function has_term_with_name(string $name): bool {
    return $this->terms->has_name($name);
  }

  /** @noinspection PhpIncompatibleReturnTypeInspection */
  public function get_public_or_private_term_binding(string $name): ?TermBinding {
    return $this->terms->get_name($name);
  }

  /** @noinspection PhpIncompatibleReturnTypeInspection */
  public function get_public_term_binding(string $name): ?TermBinding {
    return $this->terms->get_public_name($name);
  }

  public function add_term_binding(TermBinding $binding): void {
    $this->terms->add_binding($binding);
  }

  /**
   * @return TermBinding[]
   */
  public function all_public_term_bindings(): array {
    return $this->terms->get_public_bindings();
  }

  /**
   * @return TermBinding[]
   */
  public function all_public_and_private_term_bindings(): array {
    return $this->terms->get_any_bindings();
  }

  /**
   * @return string[]
   */
  public function all_public_term_names(): array {
    return $this->terms->get_public_names();
  }

  /**
   * @return string[]
   */
  public function all_public_or_private_term_names(): array {
    return $this->terms->get_any_names();
  }
}
