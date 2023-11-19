<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;

class NotSupportedError extends DOMException {
    public function __construct(?\Throwable $previous = null) {
        parent::__construct('The operation is not supported', 9, $previous);
    }
}
