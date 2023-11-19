<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


class XPathException extends \Exception {
    public const INVALID_EXPRESSION = 51;
    public const TYPE_ERROR = 52;
    public const UNRESOLVABLE_NAMESPACE_PREFIX = 53;

    public function __construct(int $code = 0, ?\Throwable $previous = null) {
        switch ($code) {
            case self::INVALID_EXPRESSION: $message = 'Invalid expression error';
            break;
            case self::TYPE_ERROR: $message = 'Expression cannot be converted to the specified type';
            break;
            case self::UNRESOLVABLE_NAMESPACE_PREFIX: $message = 'Unresolvable namespace prefix';
            break;
            default: throw new UnknownException();
        }

        parent::__construct($message, $code, $previous);
    }
}
