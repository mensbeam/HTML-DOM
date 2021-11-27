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
    Node
};


/** @covers \MensBeam\HTML\DOM\ParentNode */
class TestParentNode extends \PHPUnit\Framework\TestCase {
    /**
     * @covers \MensBeam\HTML\DOM\ParentNode::querySelector
     * @covers \MensBeam\HTML\DOM\ParentNode::querySelectorAll
     *
     * @covers \MensBeam\HTML\DOM\Attr::__get_value
     * @covers \MensBeam\HTML\DOM\Collection::__construct
     * @covers \MensBeam\HTML\DOM\Collection::__get_length
     * @covers \MensBeam\HTML\DOM\Collection::count
     * @covers \MensBeam\HTML\DOM\Collection::item
     * @covers \MensBeam\HTML\DOM\Collection::offsetGet
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_localName
     * @covers \MensBeam\HTML\DOM\Element::__get_namespaceURI
     * @covers \MensBeam\HTML\DOM\Element::__get_prefix
     * @covers \MensBeam\HTML\DOM\Element::__get_tagName
     * @covers \MensBeam\HTML\DOM\Element::getAttribute
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNode
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\ParentNode::scopeMatchSelector
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testMethod_querySelector_querySelectorAll(): void {
        $d = new Document('<!DOCTYPE html><html><body><div>ook</div><div id="eek">eek</div></body></html>');
        $div = $d->body->querySelector('div');
        $this->assertSame('div', $div->tagName);
        $this->assertNull($d->querySelector('body::before'));

        $divs = $d->body->querySelectorAll('div');
        $this->assertEquals(2, $divs->length);
        $this->assertSame('eek', $divs[1]->getAttribute('id'));
        $this->assertNull($d->querySelector('.ook'));
        $this->assertEquals(0, $d->querySelectorAll('body::before')->length);
    }


    /**
     * @covers \MensBeam\HTML\DOM\ParentNode::querySelector
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\ParentNode::scopeMatchSelector
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
    public function testMethod_querySelector__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::SYNTAX_ERROR);
        $d = new Document();
        $d->querySelector('fail?');
    }
}