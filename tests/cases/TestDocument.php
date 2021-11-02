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
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeCache::get
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeCache::has
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeCache::key
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeCache::set
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::getProtectedProperty
     */
    public function testProperty_body() {
        $d = new Document();

        // Document::body without document element
        $this->assertNull($d->body);

        $d->appendChild($d->createElement('html'));

        // Document::body without body
        $this->assertNull($d->body);

        $body = $d->documentElement->appendChild($d->createElement('body'));

        // Document::body with body
        $this->assertSame($body, $d->body);
    }

    /**
     * @covers \MensBeam\HTML\DOM\Document::__get_charset
     * @covers \MensBeam\HTML\DOM\Document::__get_inputEncoding
     */
    public function testProperty_charset() {
        $d = new Document('<!DOCTYPE html><html><head><meta charset="gb2312"></head></html>');
        $this->assertSame('GBK', $d->charset);
        $this->assertSame('GBK', $d->inputEncoding);
    }

    /** @covers \MensBeam\HTML\DOM\Document::__get_documentURI */
    public function testProperty_documentURI() {
        $d = new Document();
        $d->loadFile('https://google.com');
        $this->assertSame('https://google.com', $d->documentURI);
    }
}