<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\Framework\Exception as FrameworkException;


class Exception extends FrameworkException {
    public const DISABLED_METHOD = 301;


    public function __construct(int $code, ...$args) {
        self::$messages = array_replace(parent::$messages, [
              301 => 'Method %s has been disabled for the following reason: %s'
        ]);

        parent::__construct($code, ...$args);
    }
}
