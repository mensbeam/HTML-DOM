<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\DOMException;
use MensBeam\HTML\DOM\DOMException;


class NamespaceError extends DOMException {
    public function __construct(?\Throwable $previous = null) {
        parent::__construct('The operation is not allowed by the supplied namespace', 14, $previous);
    }
}
