<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\TestCase;

use MensBeam\HTML\DOM\{
    Document,
    Node,
    XPathEvaluator
};


/** @covers \MensBeam\HTML\DOM\XPathEvaluator */
class TestXPathEvaluator extends \PHPUnit\Framework\TestCase {
    function testMethod_registerXPathFunctions(): void {
        $d = new Document();
        $e = new XPathEvaluator();
        $this->assertNull($e->registerXPathFunctions($d));
    }


    function testMethod_xpathRegisterNamespace(): void {
        $d = new Document('<!DOCTYPE html><html><body><svg></svg></body></html>');
        $e = new XPathEvaluator();
        $e->registerXPathNamespace($d, 'svg', Node::SVG_NAMESPACE);
        $this->assertEquals(1, count($e->evaluate('//svg:svg', $d)));
    }
}