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
    XPathEvaluator
};


/** @covers \MensBeam\HTML\DOM\XPathEvaluator */
class TestXPathEvaluator extends \PHPUnit\Framework\TestCase {
    function testMethod_registerXPathFunctions(): void {
        $d = new Document();
        $e = new XPathEvaluator();
        $this->assertNull($e->registerXPathFunctions($d));
    }
}