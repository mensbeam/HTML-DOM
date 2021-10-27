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
    XMLDocument
};
use MensBeam\HTML\Parser;


/** @covers \MensBeam\HTML\DOM\Document */
class TestDocument extends \PHPUnit\Framework\TestCase {
    /**
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::getProtectedProperty
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::get
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::has
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::key
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::set
     */
    public function testProperty_body() {
        $d = new Document();
        $d->appendChild($d->createElement('html'));

        // Node::body without body
        $this->assertNull($d->body);

        $body = $d->documentElement->appendChild($d->createElement('body'));

        // Node::body with body
        $this->assertSame($body, $d->body);
    }
}