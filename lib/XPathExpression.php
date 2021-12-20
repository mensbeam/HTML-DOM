<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


class XPathExpression {
    use XPathEvaluate;

    protected string $expression;


    protected function __construct(string $expression) {
        // Test the expression by attempting to run it on an empty document. PHP's DOM
        // XPath incorrectly issues a warning on an invalid expression rather than an
        // exception, so we must use a custom error handler here to "catch" it and throw
        // an exception in its place.
        set_error_handler(function(int $errno, string $errstr, string $errfile, int $errline) {
            $lowerErrstr = strtolower($errstr);

            if (str_contains(needle: 'invalid expression', haystack: $lowerErrstr)) {
                throw new XPathException(XPathException::INVALID_EXPRESSION);
            }

            // Ignore undefined namespace prefix warnings here because there's no way to
            // register namespace prefixes before the expression is created.
        });

        $xpath = new \DOMXPath(new \DOMDocument());
        $xpath->evaluate($expression);

        restore_error_handler();

        $this->expression = $expression;
    }


    protected function evaluate(Node $contextNode, int $type = XPathResult::ANY_TYPE, ?XPathResult $result = null): XPathResult {
        return $this->xpathEvaluate($this->expression, $contextNode, $type, $result);
    }
}