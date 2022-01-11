<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\Inner\Reflection;


class XPathExpression {
    use XPathEvaluate;

    protected string $expression;
    protected ?XPathNSResolver $resolver;


    protected function __construct(string $expression, ?XPathNSResolver $resolver) {
        if ($resolver !== null && preg_match_all('/([A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}][A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}-\.0-9\x{B7}\x{0300}-\x{036F}\x{203F}-\x{2040}]+):([A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}][A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}-\.0-9\x{B7}\x{0300}-\x{036F}\x{203F}-\x{2040}]+)/u', $expression, $m, \PREG_SET_ORDER)) {
            // This part is especially nasty because of egregious use of reflection to get
            // protected properties, but neither should be exposed publicly; this is a crazy
            // polyfill hack that wouldn't normally be necessary otherwise.
            $nodeResolver = Reflection::getProtectedProperty($resolver, 'nodeResolver');
            $innerNodeResolver = $nodeResolver->innerNode;
            $doc = ($innerNodeResolver instanceof \DOMDocument) ? $innerNodeResolver : $innerNodeResolver->ownerDocument;

            foreach ($m as $prefix) {
                $prefix = $prefix[1];
                if ($namespace = $resolver->lookupNamespaceURI($prefix)) {
                    $doc->xpath->registerNamespace($prefix, $namespace);
                }
            }

            set_error_handler([ $this, 'xpathErrorHandler' ]);
            $doc->xpath->evaluate($expression);
            restore_error_handler();
        } else {
            // Test the expression by attempting to run it on an empty document. PHP's DOM
            // XPath incorrectly issues a warnings rather than exceptions when expressions
            // are incorrect, so we must use a custom error handler here to "catch" it and
            // throw an exception in its place.
            set_error_handler([ $this, 'xpathErrorHandler' ]);
            $xpath = new \DOMXPath(new \DOMDocument());
            $xpath->evaluate($expression);
            restore_error_handler();
        }

        $this->expression = $expression;
        $this->resolver = $resolver;
    }


    public function evaluate(Node $contextNode, int $type = XPathResult::ANY_TYPE, ?XPathResult $result = null): XPathResult {
        return $this->xpathEvaluate($this->expression, $contextNode, $this->resolver, $type, $result);
    }
}