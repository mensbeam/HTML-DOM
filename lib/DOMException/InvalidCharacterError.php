<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;

class InvalidCharacterError extends DOMException {
    public function __construct(?\Throwable $previous = null) {
        parent::__construct('The string contains invalid characters', 5, $previous);
    }
}
