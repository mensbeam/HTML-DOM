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


/** @covers \MensBeam\HTML\DOM\ParentNode */
class TestParentNode extends \PHPUnit\Framework\TestCase {
    /**
     * @covers \MensBeam\HTML\DOM\ParentNode::append
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createDocumentFragment
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DocumentFragment::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_innerHTML
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::convertNodesToNode
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\NonElementParentNode::getElementById
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     */
    public function testMethod_append(): void {
        $d = new Document('<!DOCTYPE html><html><body><div>ook</div><div id="eek">eek</div></body></html>');
        $eek = $d->getElementById('eek');
        $eek->append('ook', $d->createElement('br'));
        $eek->append('eek');
        $this->assertSame('eekook<br>eek', $eek->innerHTML);
    }


    /**
     * @covers \MensBeam\HTML\DOM\ParentNode::prepend
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createDocumentFragment
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DocumentFragment::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_innerHTML
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_firstChild
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::convertNodesToNode
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::insertBefore
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\NonElementParentNode::getElementById
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     */
    public function testMethod_prepend(): void {
        $d = new Document('<!DOCTYPE html><html><body><div>ook</div><div id="eek">eek</div></body></html>');
        $eek = $d->getElementById('eek');
        $eek->prepend('ook', $d->createElement('br'));
        $this->assertSame('ook<br>eek', $eek->innerHTML);
    }


    /**
     * @covers \MensBeam\HTML\DOM\ParentNode::replaceChildren
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createDocumentFragment
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DocumentFragment::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_innerHTML
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::convertNodesToNode
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\NonElementParentNode::getElementById
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     */
    public function testMethod_replaceChildren(): void {
        $d = new Document('<!DOCTYPE html><html><body><div>ook</div><div id="eek">eek</div></body></html>');
        $eek = $d->getElementById('eek');
        $eek->replaceChildren('ook', $d->createElement('br'));
        $this->assertSame('ook<br>', $eek->innerHTML);
    }


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


    /**
     * @covers \MensBeam\HTML\DOM\ParentNode::walk
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DocumentType::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::setProtectedProperties
     */
    public function testMethod_walk(): void {
        $d = new Document(<<<HTML
        <!DOCTYPE html>
        <html>
         <body>
          <div>ook</div>
          <div>
           <div><p>Eek</p></div>
           <div>
            <div><p>Ook</p></div>
           </div>
          </div>
         </body>
        </html>
        HTML);

        // Empty filter -- walk over all nodes
        $w = $d->walk();
        $this->assertSame($d->doctype, $w->current());
        foreach ($w as $node);

        // Simple accept on element and reject everything else filter
        $w = $d->walk(function($n) {
            return ($n instanceof Element) ? Node::WALK_ACCEPT : Node::WALK_REJECT;
        });
        $this->assertSame($d->documentElement, $w->current());
        foreach ($w as $node);

        // Accept element but ignore children, simple reject otherwise
        $w = $d->walk(function($n) {
            return ($n instanceof Element) ? Node::WALK_ACCEPT | Node::WALK_SKIP_CHILDREN : Node::WALK_REJECT;
        });
        $this->assertSame($d->documentElement, $w->current());
        $this->assertNull($w->next());
    }


    /**
     * @covers \MensBeam\HTML\DOM\ParentNode::walk
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     */
    public function testMethod_walk__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::SYNTAX_ERROR);
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $w = $d->walk(function($n) {
            return 2112;
        });
        $w->current();
    }


    public function testProperty_childElementCount(): void {
        $d = new Document('<!DOCTYPE html><html><body><div>ook</div><div id="eek">eek</div></body></html>');
        $this->assertEquals(2, $d->body->childElementCount);
    }


    public function testProperty_children(): void {
        $d = new Document('<!DOCTYPE html><html><body><div>ook</div><div id="eek">eek</div></body></html>');
        $this->assertEquals(2, $d->body->children->length);
    }


    public function testProperty_firstElementChild(): void {
        $d = new Document('<!DOCTYPE html><html><body>ook<div id="ook">ook</div><div id="eek">eek</div></body></html>');
        $body = $d->body;
        $this->assertSame($d->getElementById('ook'), $body->firstElementChild);
        $this->assertNull($d->getElementById('eek')->firstElementChild);
    }


    public function testProperty_lastElementChild(): void {
        $d = new Document('<!DOCTYPE html><html><body>ook<div id="ook">ook</div><div id="eek">eek</div><div id="ack">ack</div></body></html>');
        $body = $d->body;
        $this->assertSame($d->getElementById('ack'), $body->lastElementChild);
        $this->assertNull($d->getElementById('eek')->lastElementChild);
    }
}