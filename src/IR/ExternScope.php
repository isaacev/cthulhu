<?php

namespace Cthulhu\IR;

class ExternScope extends Scope {
  function add_native_module(NativeModule $module): void {
    $this->add_binding(Binding::for_module($module->scope()));
  }
}
