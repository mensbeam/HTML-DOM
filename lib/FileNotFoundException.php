<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;

class FileNotFoundException extends IOException {
    public function __construct(int $code = 0, \Throwable $previous = null, string $path = null) {
        parent::__construct(($path === null) ? 'File could not be found' : sprintf('File "%s" could not be found', $path), $code, $previous);
    }
}