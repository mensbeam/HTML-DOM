<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\TestCase;

use MensBeam\HTML\DOM\{
    Attr,
    Document,
    Node
};


/** @covers \MensBeam\HTML\DOM\NamedNodeMap */
class TestNamedNodeMap extends \PHPUnit\Framework\TestCase {
    public function testMethod_getOffset(): void {
        $d = new Document('<!DOCTYPE html><html><body a="ook" b="eek" c="ook" d="eek" e="ook"></body></html>', 'UTF-8');
        $body = $d->body;
        $body->setAttributeNS(Node::XMLNS_NAMESPACE, 'xmlns:href', Node::HTML_NAMESPACE);
        $body->setAttributeNS('https://poop💩.poop', 'poop💩:poop💩', 'poop💩');

        $attributes = $body->attributes;
        $this->assertSame('ook', $attributes['a']->value);
        $this->assertSame(Node::HTML_NAMESPACE, $attributes['xmlns:href']->value);
        $this->assertSame('poop💩', $attributes['poop💩:poop💩']->value);
    }
}