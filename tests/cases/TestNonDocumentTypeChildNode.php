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
    Node
};


/** @covers \MensBeam\HTML\DOM\NonDocumentTypeChildNode */
class TestNonDocumentTypeChildNode extends \PHPUnit\Framework\TestCase {
    public function testProperty_nextElementSibling_previousElementSibling(): void {
        $d = new Document('<!DOCTYPE html><html><body></body></html>');
        $body = $d->body;
        $br = $body->appendChild($d->createElement('br'));
        $body->appendChild($d->createTextNode('eek'));
        $ook = $body->appendChild($d->createTextNode('ook'));
        $br2 = $body->appendChild($d->createElement('br'));

        $this->assertSame($br2, $br->nextElementSibling);
        $this->assertSame($br, $ook->previousElementSibling);
    }
}