<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\Inner\Reflection;


trait XPathEvaluatorBase {
    use XPathEvaluate;


    public function createExpression(string $expression, ?XPathNSResolver $resolver = null): XPathExpression {
        // XPathExpression cannot be created from their constructors normally.
        return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\XPathExpression', $expression, $resolver);
    }

    public function createNSResolver(Node $nodeResolver): XPathNSResolver {
        // XPathNSResolver cannot be created from their constructors normally.
        return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\XPathNSResolver', $nodeResolver);
    }

    public function evaluate(string $expression, Node $contextNode, ?XPathNSResolver $resolver = null, int $type = XPathResult::ANY_TYPE, ?XPathResult $result = null): XPathResult {
        return $this->xpathEvaluate($expression, $contextNode, $resolver, $type, $result);
    }
}