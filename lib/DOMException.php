<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\Framework\Exception;


class DOMException extends Exception {
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

    const OUTER_HTML_FAILED_NOPARENT = 301;


    public function __construct(int $code, ...$args) {
        self::$messages = array_replace(parent::$messages, [
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

            301 => 'Failed to set the "outerHTML" property; the element does not have a parent node'
        ]);

        parent::__construct($code, ...$args);
    }
}
