<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;

class SyntaxError extends DOMException {
    public function __construct(?\Throwable $previous = null) {
        parent::__construct('The string did not match the expected pattern', 12, $previous);
    }
}
