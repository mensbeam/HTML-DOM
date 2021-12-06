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
    Node
};


/** @covers \MensBeam\HTML\DOM\Attr */
class TestAttr extends \PHPUnit\Framework\TestCase {
    public function testProperty_name(): void {
        $d = new Document('<!DOCTYPE html><html><body ook="ook" poop💩="poop💩"><svg id="eek"></svg></body></html>', 'utf-8');
        $body = $d->body;
        $svg = $d->getElementById('eek');
        $svg->setAttributeNS(Node::XMLNS_NAMESPACE, 'xmlns:xlink', Node::XLINK_NAMESPACE);

        // HTML name
        $this->assertSame('ook', $body->getAttributeNode('ook')->name);
        // Coerced name
        $this->assertSame('poop💩', $body->getAttributeNode('poop💩')->name);
        // Foreign attribute name
        $this->assertSame('xlink', $svg->getAttributeNodeNS(Node::XMLNS_NAMESPACE, 'xlink')->name);
    }

    public function testProperty_prefix(): void {
        $d = new Document('<!DOCTYPE html><html><body><svg id="eek"></svg></body></html>', 'utf-8');
        $svg = $d->getElementById('eek');
        $svg->setAttributeNS(Node::XMLNS_NAMESPACE, 'xmlns:xlink', Node::XLINK_NAMESPACE);
        $svg->setAttributeNS('https://poop.poop', 'poop💩:poop💩', 'poop💩');

        // Foreign attribute name
        $this->assertSame('xmlns', $svg->getAttributeNodeNS(Node::XMLNS_NAMESPACE, 'xlink')->prefix);
        $this->assertSame('poop💩', $svg->getAttributeNodeNS('https://poop.poop', 'poop💩')->prefix);
    }

    public function testProperty_specified(): void {
        $d = new Document('<!DOCTYPE html><html><body ook="ook"></body></html>', 'utf-8');
        $this->assertTrue($d->body->getAttributeNode('ook')->specified);
    }
}