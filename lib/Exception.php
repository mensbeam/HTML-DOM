<?php
declare(strict_types=1);
namespace dW\HTML5;

class Exception extends \Exception {
    const INVALID_CODE = 10000;
    const UNKNOWN_ERROR = 10001;
    const INCORRECT_PARAMETERS_FOR_MESSAGE = 10002;

    const PARSER_DOMDOCUMENT_EXPECTED = 10101;
    const PARSER_DOMELEMENT_DOMDOCUMENT_DOMDOCUMENTFRAG_EXPECTED = 10102;
    const PARSER_DOMNODE_EXPECTED = 10103;

    const STACK_INVALID_INDEX = 10201;
    const STACK_DOMNODE_ONLY = 10202;

    const DATASTREAM_NODATA = 10301;
    const DATASTREAM_INVALID_DATA_CONSUMPTION_LENGTH = 10302;

    const DOM_DOMELEMENT_STRING_OR_CLOSURE_EXPECTED = 10401;

    protected static $messages = [10000 => 'Invalid error code',
                                  10001 => 'Unknown error; escaping',
                                  10002 => 'Incorrect number of parameters for Exception message; %s expected',

                                  10101 => 'DOMDocument expected; found %s',
                                  10102 => 'DOMElement, DOMDocument, or DOMDocumentFrag expected; found %s',
                                  10103 => 'DOMNode expected; found %s',

                                  10201 => '%s is an invalid index',
                                  10202 => 'Instances of DOMNode are the only types allowed in an HTML5Stack',

                                  10301 => 'Data string expected; found %s',
                                  10302 => '%s is an invalid data consumption length; a value of 1 or above is expected',

                                  10401 => 'The first argument must either be an instance of \DOMElement, a string, or a closure; found %s'];

    public function __construct(int $code, ...$args) {
        if (!isset(static::$messages[$code])) {
            throw new Exception(static::INVALID_CODE);
        }

        $message = static::$messages[$code];
        $previous = null;

        // Grab a previous exception if there is one.
        if ($args[0] instanceof \Throwable) {
            $previous = array_shift($args);
        } elseif (end($args) instanceof \Throwable) {
            $previous = array_pop($args);
        }

        // Count the number of replacements needed in the message.
        $count = substr_count($message, '%s');
        // If the number of replacements don't match the arguments then oops.
        if (count($args) !== $count) {
            throw new Exception(static::INCORRECT_PARAMETERS_FOR_MESSAGE, $count);
        }

        if ($count > 0) {
            // Convert newlines and tabs in the arguments to words to better express what they
            // are.
            /*$args = array_map(function($value) {
                switch ($value) {
                    case "\n": return 'Newline';
                    break;
                    case "\t": return 'Tab';
                    break;
                    default: return $value;
                }
            }, $args);*/

            // Go through each of the arguments and run sprintf on the strings.
            $message = call_user_func_array('sprintf', array_merge([$message], $args));
        }

        parent::__construct($message, $code, $previous);
    }
}