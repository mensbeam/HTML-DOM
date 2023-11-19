<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;

class UnknownException extends \LogicException {
    public function __construct(int $code = 0, ?\Throwable $previous = null) {
        parent::__construct('The program reached an invalid state; this error should be reported', $code, $previous);
    }
}