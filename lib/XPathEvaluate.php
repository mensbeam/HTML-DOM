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
    protected function xpathEvaluate(string $expression, Node $contextNode, int $type = XPathResult::ANY_TYPE, ?XPathResult $result = null): XPathResult {
        $innerContextNode = Reflection::getProtectedProperty($contextNode, 'innerNode');
        $doc = ($innerContextNode instanceof \DOMDocument) ? $innerContextNode : $innerContextNode->ownerDocument;

        set_error_handler(function(int $errno, string $errstr, string $errfile, int $errline) {
            $lowerErrstr = strtolower($errstr);

            if (str_contains(needle: 'invalid expression', haystack: $lowerErrstr)) {
                throw new XPathException(XPathException::INVALID_EXPRESSION);
            }

            if (str_contains(needle: 'undefined namespace prefix', haystack: $lowerErrstr)) {
                throw new XPathException(XPathException::UNDEFINED_NAMESPACE_PREFIX);
            }
        });
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
        Reflection::getProtectedProperty($document, 'innerNode')->xpath->registerPhpFunctions($restrict);
    }

    protected function xpathRegisterNamespace(Document $document, string $prefix, string $namespace): bool {
        return Reflection::getProtectedProperty($document, 'innerNode')->xpath->registerNamespace($prefix, $namespace);
    }
}