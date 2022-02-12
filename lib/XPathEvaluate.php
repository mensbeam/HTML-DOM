<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\Inner\Reflection;


trait XPathEvaluate {
    protected function xpathErrorHandler(int $errno, string $errstr, string $errfile, int $errline) {
        $lowerErrstr = strtolower($errstr);
        if (str_contains(needle: 'invalid expression', haystack: $lowerErrstr)) {
            throw new XPathException(XPathException::INVALID_EXPRESSION);
        }

        if (str_contains(needle: 'undefined namespace prefix', haystack: $lowerErrstr)) {
            throw new XPathException(XPathException::UNRESOLVABLE_NAMESPACE_PREFIX);
        }
    } // @codeCoverageIgnore

    protected function xpathEvaluate(string $expression, Node $contextNode, \Closure|XPathNSResolver|null $resolver = null, int $type = XPathResult::ANY_TYPE, ?XPathResult $result = null): XPathResult {
        $innerContextNode = $contextNode->innerNode;
        $doc = ($innerContextNode instanceof \DOMDocument) ? $innerContextNode : $innerContextNode->ownerDocument;

        if ($resolver !== null && preg_match_all('/([A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}][A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}-\.0-9\x{B7}\x{0300}-\x{036F}\x{203F}-\x{2040}]+):([A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}][A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}-\.0-9\x{B7}\x{0300}-\x{036F}\x{203F}-\x{2040}]*)/u', $expression, $m, \PREG_SET_ORDER)) {
            foreach ($m as $prefix) {
                $prefix = $prefix[1];

                if ($resolver instanceof XPathNSResolver) {
                    $namespace = $contextNode->lookupNamespaceURI($prefix);
                } elseif ($namespace = $resolver($prefix)) {
                    $namespace = (string)$namespace;
                }

                if ($namespace !== null) {
                    $doc->xpath->registerNamespace($prefix, $namespace);
                }
            }
        }

        // PHP's DOM XPath incorrectly issues warnings rather than exceptions when
        // expressions are incorrect, so we must use a custom error handler here to
        // "catch" it and throw an exception in its place.
        set_error_handler([ $this, 'xpathErrorHandler' ]);
        $result = $doc->xpath->evaluate($expression, $innerContextNode);
        restore_error_handler();

        if ($type === XPathResult::ANY_TYPE) {
            $typeOfResult = gettype($result);
            if ($typeOfResult === 'object') {
                $typeOfResult = $result::class;
            }

            switch ($typeOfResult) {
                case 'integer':
                case 'double':
                    $resultType = XPathResult::NUMBER_TYPE;
                break;
                case 'string':
                    $resultType = XPathResult::STRING_TYPE;
                break;
                case 'boolean':
                    $resultType = XPathResult::BOOLEAN_TYPE;
                break;
                case 'DOMNodeList':
                    $resultType = XPathResult::ORDERED_NODE_ITERATOR_TYPE;
                break;
                default:
                    throw new DOMException(DOMException::NOT_SUPPORTED);
            }
        } else {
            switch ($type) {
                case XPathResult::NUMBER_TYPE:
                    if ($result instanceof \DOMNodeList) {
                        throw new XPathException(XPathException::TYPE_ERROR);
                    }

                    $result = (float)$result;
                break;

                case XPathResult::STRING_TYPE:
                    if ($result instanceof \DOMNodeList) {
                        throw new XPathException(XPathException::TYPE_ERROR);
                    }

                    $result = (string)$result;
                break;

                case XPathResult::BOOLEAN_TYPE:
                    if ($result instanceof \DOMNodeList) {
                        $result = ($result->length > 0);
                    }

                    $result = (bool)$result;
                break;

                // In this implementation there's no difference between these because PHP's
                // XPath DOM (ALMOST!) always returns in document order, and that cannot be
                // changed.
                case XPathResult::UNORDERED_NODE_ITERATOR_TYPE:
                case XPathResult::ORDERED_NODE_ITERATOR_TYPE:
                    if (!$result instanceof \DOMNodeList) {
                        throw new XPathException(XPathException::TYPE_ERROR);
                    }
                break;

                // In this implementation there's no difference between these because PHP's
                // XPath DOM (ALMOST!) always returns in document order, and that cannot be
                // changed.
                case XPathResult::UNORDERED_NODE_SNAPSHOT_TYPE:
                case XPathResult::ORDERED_NODE_SNAPSHOT_TYPE:
                    if (!$result instanceof \DOMNodeList) {
                        throw new XPathException(XPathException::TYPE_ERROR);
                    }

                    $temp = [];
                    foreach ($result as $node) {
                        $temp[] = $node;
                    }
                    $result = $temp;
                break;

                // In this implementation there's no difference between these because PHP's
                // XPath DOM (ALMOST!) always returns in document order, and that cannot be
                // changed.
                case XPathResult::ANY_UNORDERED_NODE_TYPE:
                case XPathResult::FIRST_ORDERED_NODE_TYPE:
                    if (!$result instanceof \DOMNodeList) {
                        throw new XPathException(XPathException::TYPE_ERROR);
                    }

                    $result = $result->item(0);
                break;

                default: throw new DOMException(DOMException::NOT_SUPPORTED);
            }

            $resultType = $type;
        }

        // XPathResult cannot be created from their constructors normally.
        return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\XPathResult', $resultType, ($result instanceof \DOMNodeList || is_array($result)) ? $result : [ $result ]);
    }

    protected function xpathRegisterPhpFunctions(Document $document, string|array|null $restrict = null): void {
        $xpath = $document->innerNode->xpath;
        $xpath->registerNamespace('php', 'http://php.net/xpath');
        $xpath->registerPhpFunctions($restrict);
    }
}