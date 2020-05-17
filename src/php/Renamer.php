<?php

namespace Cthulhu\php;

use Cthulhu\ir\nodes as ir;
use Cthulhu\lib\trees\Visitor;

class Renamer {
  private Names $names;
  private array $pending_names = [];

  private function __construct() {
    $this->names = new Names();
  }

  private function open_namespace(ir\Name $name) {
    $ref = $this->names->name_to_ref($name);
    array_push($this->pending_names, $ref);
    $this->names->enter_namespace_scope();
  }

  private function close_namespace() {
    array_pop($this->pending_names);
    $this->names->exit_namespace_scope();
  }

  private function open_anonymous_namespace() {
    $this->names->enter_namespace_scope();
  }

  private function close_anonymous_namespace() {
    $this->names->exit_namespace_scope();
  }

  private function current_ref(): nodes\Reference {
    assert(!empty($this->pending_names));
    return end($this->pending_names);
  }

  public static function rename(ir\Root $root): void {
    $ctx = new self();

    Visitor::walk($root, [
      'enter(Module)' => function (ir\Module $mod) use ($ctx) {
        if ($mod->name) {
          $ctx->open_namespace($mod->name);
        } else {
          $ctx->open_anonymous_namespace();
        }
      },
      'exit(Module)' => function (ir\Module $mod) use ($ctx) {
        if ($mod->name) {
          $ctx->close_namespace();
        } else {
          $ctx->close_anonymous_namespace();
        }
      },

      'Enum' => function (ir\Enum $enum) use ($ctx) {
        $ctx->names->name_to_ref_name($enum->name, $ctx->current_ref());
        $ctx->names->name_to_ref($enum->name);
        foreach ($enum->forms as $form) {
          $ctx->names->name_to_ref_name($form->name, $ctx->current_ref());
        }
      },

      'enter(Def)' => function (ir\Def $def) use ($ctx) {
        $ctx->names->name_to_ref_name($def->name, $ctx->current_ref());
      },
    ]);
  }
}
