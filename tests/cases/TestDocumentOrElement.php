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
    DOMException,
    Element,
    Node,
    Text,
    XMLDocument
};
use MensBeam\HTML\Parser;


/** @covers \MensBeam\HTML\DOM\DocumentOrElement */
class TestDocumentOrElement extends \PHPUnit\Framework\TestCase {
    public function testMethod_getElementsByClassName() {
        $d = new Document('<!DOCTYPE html><html><body><div class="ook eek"><span class="ook eek"><i class="ookeek eek"></i></span></div></body></html>');

        // Empty class
        $ook = $d->getElementsByClassName('');
        $this->assertEquals(0, $ook->length);

        // Document context node
        $ook = $d->getElementsByClassName('ook');
        $this->assertEquals(2, $ook->length);

        // Element context node
        $div = $ook[0];
        $ook = $div->getElementsByClassName('ook');
        $this->assertSame('DIV', $div->nodeName);
        $this->assertEquals(1, $ook->length);
        $this->assertSame('SPAN', $ook[0]->nodeName);

        // Multiple classes
        $ook = $d->getElementsByClassName('ook eek');
        $this->assertEquals(2, $ook->length);
    }
}