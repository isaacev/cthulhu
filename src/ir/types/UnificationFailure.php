<?php

namespace Cthulhu\ir\types;

use Exception;

/**
 * The UnificationFailure exception is used exclusively inside of the
 * {@link TypeChecker} class to signal when the two types are incompatible.
 *
 * The exception should NOT be allowed to escape the type-checker and should be
 * caught inside the type-checker and the appropriate {@link \Cthulhu\err\Error}
 * should be thrown instead.
 */
class UnificationFailure extends Exception {
  // empty
}
