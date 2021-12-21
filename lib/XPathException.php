<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\Framework\Exception;


class XPathException extends Exception {
    public const INVALID_EXPRESSION = 51;
    public const TYPE_ERROR = 52;
    public const UNRESOLVABLE_NAMESPACE_PREFIX = 53;

    public function __construct(int $code, ...$args) {
        self::$messages = array_replace(parent::$messages, [
              51 => 'Invalid expression error',
              52 => 'Expression cannot be converted to the specified type',
              53 => 'Unresolvable namespace prefix'
        ]);

        parent::__construct($code, ...$args);
    }
}
