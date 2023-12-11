<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\DOMException;
use MensBeam\HTML\DOM\DOMException;


class NoModificationAllowedError extends DOMException {
    public function __construct(?\Throwable $previous = null) {
        parent::__construct('The object can not be modified', 7, $previous);
    }
}
