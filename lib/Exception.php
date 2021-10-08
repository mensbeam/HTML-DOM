<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;

class Exception extends \Exception {
    const INVALID_CODE = 100;
    const UNKNOWN_ERROR = 101;
    const INCORRECT_PARAMETERS_FOR_MESSAGE = 102;
    const UNREACHABLE_CODE = 103;

    const NONEXISTENT_PROPERTY = 201;
    const READONLY_PROPERTY = 202;
    const ARGUMENT_TYPE_ERROR = 203;


    protected static $messages = [
        100 => 'Invalid error code',
        101 => 'Unknown error; escaping',
        102 => 'Incorrect number of parameters for Exception message; %s expected',
        103 => 'Unreachable code',

        201 => 'Property %s does not exist',
        202 => 'Cannot write readonly property %s',
        203 => 'Argument #%s ($%s) must be of type %s, %s given'
    ];

    public function __construct(int $code, ...$args) {
        if (!isset(self::$messages[$code])) {
            throw new Exception(Exception::INVALID_CODE);
        }

        $message = self::$messages[$code];
        $previous = null;

        // @codeCoverageIgnoreStart
        if ($args) {
            // Grab a previous exception if there is one.
            if ($args[0] instanceof \Throwable) {
                $previous = array_shift($args);
            } elseif (end($args) instanceof \Throwable) {
                $previous = array_pop($args);
            }
        }
        // @codeCoverageIgnoreEnd

        // Count the number of replacements needed in the message.
        preg_match_all('/(\%(?:\d+\$)?s)/', $message, $matches);
        $count = count($matches[1]);

        // If the number of replacements don't match the arguments then oops.
        if (count($args) !== $count) {
            throw new Exception(Exception::INCORRECT_PARAMETERS_FOR_MESSAGE, $count);
        }

        if ($count > 0) {
            // Go through each of the arguments and run sprintf on the strings.
            $message = call_user_func_array('sprintf', array_merge([$message], $args));
        }

        parent::__construct($message, $code, $previous);
    }
}
