<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;

class DOMException extends \Exception {
    // From PHP's DOMException; keeping error codes consistent
    const HIERARCHY_REQUEST_ERROR = 3;
    const WRONG_DOCUMENT = 4;
    const INVALID_CHARACTER = 5;
    const NO_MODIFICATION_ALLOWED = 7;
    const NOT_FOUND = 8;
    const NOT_SUPPORTED = 9;
    const SYNTAX_ERROR = 12;
    const INVALID_MODIFICATION_ERROR = 13;
    const NAMESPACE_ERROR = 14;
    const INVALID_ACCESS_ERROR = 15;
    const VALIDATION_ERROR = 16;

    const OUTER_HTML_FAILED_NOPARENT = 101;


    protected static $messages = [
          3 => 'Hierarchy request error; supplied node is not allowed here',
          4 => 'Supplied node does not belong to this document',
          5 => 'Invalid character',
          7 => 'Modification not allowed here',
          8 => 'Not found error',
          9 => 'Feature is not supported because %s',
         12 => 'Syntax error',
         13 => 'Invalid modification error',
         14 => 'Namespace error',
         15 => 'Invalid access error',
         16 => 'Validation error',

        101 => 'Failed to set the "outerHTML" property; the element does not have a parent node'
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
