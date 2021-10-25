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
    /**
     * @covers \MensBeam\HTML\DOM\Node::__get_childNodes
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\NodeList::__construct
     * @covers \MensBeam\HTML\DOM\NodeList::count
     * @covers \MensBeam\HTML\DOM\NodeList::__get_length
     * @covers \MensBeam\HTML\DOM\NodeList::item
     * @covers \MensBeam\HTML\DOM\NodeList::offsetGet
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
        // Try it again to test caching in coverage; no reason to assert
        $childNodes = $d->body->lastChild->childNodes;
    }


    /**
     * @covers \MensBeam\HTML\DOM\Node::__get_firstChild
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
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
    public function testProperty_firstChild() {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $de = $d->documentElement;
        $de->appendChild($d->createElement('body'));

        // Node::firstChild on Document
        $this->assertSame($de, $d->firstChild);
        // Node::firstChild on document element
        $this->assertSame($d->body, $de->firstChild);
        // Node::firstChild on empty node
        $this->assertNull($d->body->firstChild);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Node::__get_isConnected
     *
     * @covers \MensBeam\HTML\DOM\ChildNode::moonwalk
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
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
    public function testProperty_isConnected() {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $b = $d->createElement('body');

        $this->assertTrue($d->documentElement->isConnected);
        $this->assertFalse($b->isConnected);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Node::__get_lastChild
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
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
    public function testProperty_lastChild() {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $de = $d->documentElement;
        $de->appendChild($d->createElement('body'));
        $b = $d->body;
        $b->appendChild($d->createElement('span'));
        $o = $b->appendChild($d->createTextNode('ook'));

        // Node::lastChild on Document
        $this->assertSame($de, $d->lastChild);
        // Node::lastChild on document element
        $this->assertSame($d->body, $de->lastChild);
        // Node::lastChild on element with multiple children
        $this->assertSame($o, $b->lastChild);
        // Node::lastChild on text node
        $this->assertNull($o->lastChild);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Node::__get_previousSibling
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createDocumentType
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
    public function testProperty_previousSibling() {
        $d = new Document();
        $dt = $d->appendChild($d->implementation->createDocumentType('html', '', ''));
        $d->appendChild($d->createElement('html'));
        $de = $d->documentElement;
        $de->appendChild($d->createElement('body'));
        $b = $d->body;
        $s = $b->appendChild($d->createElement('span'));
        $o = $b->appendChild($d->createTextNode('ook'));

        // Node::previousSibling on document element
        $this->assertSame($dt, $de->previousSibling);
        // Node::previousSibling on element with multiple children
        $this->assertSame($s, $o->previousSibling);
        // Node::previousSibling on first child of body
        $this->assertNull($b->firstChild->previousSibling);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Node::__get_nextSibling
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createDocumentType
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
    public function testProperty_nextSibling() {
        $d = new Document();
        $dt = $d->appendChild($d->implementation->createDocumentType('html', '', ''));
        $d->appendChild($d->createElement('html'));
        $de = $d->documentElement;
        $de->appendChild($d->createElement('body'));
        $b = $d->body;
        $s = $b->appendChild($d->createElement('span'));
        $o = $b->appendChild($d->createTextNode('ook'));

        // Node::nextSibling on doctype
        $this->assertSame($de, $dt->nextSibling);
        // Node::nextSibling on element with multiple children
        $this->assertSame($o, $s->nextSibling);
        // Node::nextSibling on last child of body
        $this->assertNull($b->lastChild->nextSibling);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Node::__get_nodeName
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createDocumentType
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
    public function testProperty_nodeName() {
        $d = new Document();
        $dt = $d->appendChild($d->implementation->createDocumentType('html', '', ''));

        // Node::nodeName on element
        $this->assertSame('HTML', $d->createElement('html')->nodeName);
        // Node::nodeName on element with coerced name
        $this->assertSame('POOP💩', $d->createElement('poop💩')->nodeName);
    }
}