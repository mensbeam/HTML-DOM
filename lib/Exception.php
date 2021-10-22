<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\Framework\Exception as FrameworkException;


class DOMException extends FrameworkException {
    public const CLIENT_ONLY_NOT_IMPLEMENTED = 301;


    public function __construct(int $code, ...$args) {
        self::$messages = array_replace(parent::$messages, [
             301 => '%s is client side only; not implemented'
        ]);

        parent::__construct($code, ...$args);
    }
}
