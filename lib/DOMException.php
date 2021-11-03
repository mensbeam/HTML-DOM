<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\Framework\Exception;


class DOMException extends Exception {
    public const INDEX_SIZE_ERROR = 1;
    public const HIERARCHY_REQUEST_ERROR = 3;
    public const WRONG_DOCUMENT = 4;
    public const INVALID_CHARACTER = 5;
    public const NO_MODIFICATION_ALLOWED = 7;
    public const NOT_FOUND = 8;
    public const NOT_SUPPORTED = 9;
    public const SYNTAX_ERROR = 12;
    public const INVALID_MODIFICATION = 13;
    public const NAMESPACE_ERROR = 14;
    public const INVALID_ACCESS = 15;

    public const FILE_NOT_FOUND = 301;


    public function __construct(int $code, ...$args) {
        self::$messages = array_replace(parent::$messages, [
              1 => 'Invalid index size',
              3 => 'Hierarchy request error',
              4 => 'Supplied node does not belong to this document',
              5 => 'Invalid character',
              7 => 'Modification not allowed here',
              8 => 'Not found error',
              9 => 'Feature is not supported',
             12 => 'Syntax error',
             13 => 'Invalid modification error',
             14 => 'Namespace error',
             15 => 'Invalid access error',

             301 => 'File not found'
        ]);

        parent::__construct($code, ...$args);
    }
}
