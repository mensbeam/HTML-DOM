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
use MensBeam\HTML\Parser;


/** @covers \MensBeam\HTML\DOM\Document */
class TestNode extends \PHPUnit\Framework\TestCase {
    /** @covers \MensBeam\HTML\DOM\Node::__get_childNodes */
    public function testProperty_childNodes() {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $b = $d->body;
        $b->appendChild($d->createElement('span'));
        $b->appendChild($d->createTextNode('ook'));

        // Node::childNodes on Element
        $childNodes = $d->body->childNodes;
        $this->assertEquals(2, $childNodes->length);
        $this->assertSame('SPAN', $childNodes[0]->nodeName);

        // Node::childNodes on Text
        $childNodes = $d->body->lastChild->childNodes;
        $this->assertEquals(0, $childNodes->length);
        // Try it again to test caching
        $childNodes = $d->body->lastChild->childNodes;
    }
}