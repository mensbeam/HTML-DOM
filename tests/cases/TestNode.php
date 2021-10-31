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


/** @covers \MensBeam\HTML\DOM\Node */
class TestNode extends \PHPUnit\Framework\TestCase {
    /**
     * @covers \MensBeam\HTML\DOM\Node::cloneNode
     *
     * @covers \MensBeam\HTML\DOM\Attr::__set_value
     * @covers \MensBeam\HTML\DOM\Comment::__construct
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::__get_characterSet
     * @covers \MensBeam\HTML\DOM\Document::__get_compatMode
     * @covers \MensBeam\HTML\DOM\Document::__get_contentType
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::__get_implementation
     * @covers \MensBeam\HTML\DOM\Document::__get_URL
     * @covers \MensBeam\HTML\DOM\Document::__createAttribute
     * @covers \MensBeam\HTML\DOM\Document::createAttribute
     * @covers \MensBeam\HTML\DOM\Document::createCDATASection
     * @covers \MensBeam\HTML\DOM\Document::createComment
     * @covers \MensBeam\HTML\DOM\Document::createDocumentFragment
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createProcessingInstruction
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DocumentFragment::__construct
     * @covers \MensBeam\HTML\DOM\DocumentType::__construct
     * @covers \MensBeam\HTML\DOM\DocumentType::__get_name
     * @covers \MensBeam\HTML\DOM\DocumentType::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\DocumentType::__get_publicId
     * @covers \MensBeam\HTML\DOM\DocumentType::__get_systemId
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createDocumentType
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::setAttribute
     * @covers \MensBeam\HTML\DOM\HTMLTemplateElement::__construct
     * @covers \MensBeam\HTML\DOM\HTMLTemplateElement::__get_content
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::cloneInnerNode
     * @covers \MensBeam\HTML\DOM\Node::cloneWrapperNode
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::isEqualInnerNode
     * @covers \MensBeam\HTML\DOM\Node::isEqualNode
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\ProcessingInstruction::__construct
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::get
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::has
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::key
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::set
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::getProtectedProperty
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::setProtectedProperties
     */
    public function testMethod_cloneNode() {
        $d = new Document();
        $d2 = new XMLDocument();
        $doctype = $d->appendChild($d->implementation->createDocumentType('html', '', ''));
        $element = $d->appendChild($d->createElement('html'));
        $element->appendChild($d->createElement('body'));
        $d->body->setAttribute('id', 'ook');
        $template = $d->body->appendChild($d->createElement('template'));
        $template->content->appendChild($d->createTextNode('ook'));
        $attr = $d->createAttribute('href');
        $attr->value = 'https://poop💩.poop';
        $cdata = $d2->createCDATASection('ook');
        $comment = $d->createComment('comment');
        $pi = $d->createProcessingInstruction('ook', 'eek');
        $text = $d->createTextNode('ook');
        $frag = $d->createDocumentFragment();
        $frag->appendChild($d->createTextNode('ook'));

        // Node::cloneNode on attribute node
        $attrClone = $attr->cloneNode();
        $this->assertNotSame($attrClone, $attr);
        $this->assertTrue($attrClone->isEqualNode($attr));

        // Node::cloneNode on CDATA section
        $cdataClone = $cdata->cloneNode();
        $this->assertNotSame($cdataClone, $cdata);
        $this->assertTrue($cdataClone->isEqualNode($cdata));

        // Node::cloneNode on comment
        $commentClone = $comment->cloneNode();
        $this->assertNotSame($commentClone, $comment);
        $this->assertTrue($commentClone->isEqualNode($comment));

        // Node::cloneNode on document
        $dClone = $d->cloneNode(true);
        $this->assertNotSame($dClone, $d);
        $this->assertTrue($dClone->isEqualNode($d));

        // Node::cloneNode on doctype
        $doctypeClone = $doctype->cloneNode();
        $this->assertNotSame($doctypeClone, $doctype);
        $this->assertTrue($doctypeClone->isEqualNode($doctype));

        // Node::cloneNode on document fragment
        $fragClone = $frag->cloneNode(true);
        $this->assertNotSame($fragClone, $frag);
        $this->assertTrue($fragClone->isEqualNode($frag));

        // Node::cloneNode on element
        $elementClone = $element->cloneNode(true);
        $this->assertNotSame($elementClone, $element);
        $this->assertTrue($elementClone->isEqualNode($element));

        // Node::cloneNode on processing instruction
        $piClone = $pi->cloneNode();
        $this->assertNotSame($piClone, $pi);
        $this->assertTrue($piClone->isEqualNode($pi));

        // Node::cloneNode on text node
        $textClone = $text->cloneNode();
        $this->assertNotSame($textClone, $text);
        $this->assertTrue($textClone->isEqualNode($text));
    }


    /**
     * @covers \MensBeam\HTML\DOM\Node::compareDocumentPosition
     *
     * @covers \MensBeam\HTML\DOM\Collection::__construct
     * @covers \MensBeam\HTML\DOM\Collection::item
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::getElementById
     * @covers \MensBeam\HTML\DOM\Document::getElementsByTagName
     * @covers \MensBeam\HTML\DOM\Document::loadHTML
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_namespaceURI
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNode
     * @covers \MensBeam\HTML\DOM\HTMLCollection::item
     * @covers \MensBeam\HTML\DOM\HTMLCollection::offsetGet
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::__get_parentNode
     * @covers \MensBeam\HTML\DOM\Node::cloneInnerNode
     * @covers \MensBeam\HTML\DOM\Node::containsInner
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::removeChild
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::get
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::has
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::key
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::set
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::getProtectedProperty
     */
    public function testMethod_compareDocumentPosition(): void {
        $d = new Document('<!DOCTYPE html><html><body><header><h1>Ook</h1></header><main><h2 id="eek" class="ack">Eek</h2><p>Ook <a href="ook">eek</a>, ook?</p></main><footer></footer></body></html>');

        $body = $d->body;
        $main = $d->getElementsByTagName('main')[0];
        $footer = $d->getElementsByTagName('footer')[0];
        $eek = $d->getElementById('eek');
        $h2Id = $eek->getAttributeNode('id');
        $h2Class = $eek->getAttributeNode('class');
        $aHref = $d->getElementsByTagName('a')[0]->getAttributeNode('href');

        // Compare main element to body element
        $compareMainToBody = $main->compareDocumentPosition($body);
        $this->assertEquals(10, $compareMainToBody);
        // Compare body element to main element
        $compareBodyToMain = $body->compareDocumentPosition($main);
        $this->assertEquals(20, $compareBodyToMain);
        // Compare footer element to main element
        $compareFooterToMain = $footer->compareDocumentPosition($main);
        $this->assertEquals(2, $compareFooterToMain);
        // Compare main element to footer element
        $compareMainToFooter = $main->compareDocumentPosition($footer);
        $this->assertEquals(4, $compareMainToFooter);
        // Compare h2 element id attribute to a element href attribute
        $compareH2IdToAHref = $h2Id->compareDocumentPosition($aHref);
        $this->assertEquals(4, $compareH2IdToAHref);
        // Compare h2 element id attribute to a h2 element class attribute
        $compareH2IdToH2Class = $h2Id->compareDocumentPosition($h2Class);
        $this->assertEquals(36, $compareH2IdToH2Class);
        // Compare h2 element id attribute to a h2 element class attribute
        $compareH2ClassToH2Id = $h2Class->compareDocumentPosition($h2Id);
        $this->assertEquals(34, $compareH2ClassToH2Id);
        // Compare main element to itself
        $this->assertEquals(0, $main->compareDocumentPosition($main));

        $this->assertGreaterThan(0, $compareMainToBody & Document::DOCUMENT_POSITION_CONTAINS);
        $this->assertGreaterThan(0, $compareMainToBody & Document::DOCUMENT_POSITION_PRECEDING);
        $this->assertEquals(0, $compareMainToBody & Document::DOCUMENT_POSITION_FOLLOWING);

        $this->assertGreaterThan(0, $compareBodyToMain & Document::DOCUMENT_POSITION_CONTAINED_BY);
        $this->assertGreaterThan(0, $compareBodyToMain & Document::DOCUMENT_POSITION_FOLLOWING);
        $this->assertEquals(0, $compareBodyToMain & Document::DOCUMENT_POSITION_PRECEDING);

        $this->assertGreaterThan(0, $compareFooterToMain & Document::DOCUMENT_POSITION_PRECEDING);
        $this->assertGreaterThan(0, $compareMainToFooter & Document::DOCUMENT_POSITION_FOLLOWING);

        $this->assertGreaterThan(0, $compareH2IdToAHref & Document::DOCUMENT_POSITION_FOLLOWING);
        $this->assertGreaterThan(0, $compareH2IdToH2Class & Document::DOCUMENT_POSITION_FOLLOWING);
        $this->assertGreaterThan(0, $compareH2ClassToH2Id & Document::DOCUMENT_POSITION_PRECEDING);

        $main->parentNode->removeChild($main);
        $compareDetachedMainToFooter = $main->compareDocumentPosition($footer);
        $this->assertEquals($compareDetachedMainToFooter, $main->compareDocumentPosition($footer));
        $this->assertGreaterThanOrEqual(35, $compareDetachedMainToFooter);
        $this->assertLessThanOrEqual(37, $compareDetachedMainToFooter);
        $this->assertNotEquals(36, $compareDetachedMainToFooter);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Node::contains
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::containsInner
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::get
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::has
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::key
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::set
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::getProtectedProperty
     */
    public function testMethod_contains(): void {
        $d = new Document();
        $documentElement = $d->appendChild($d->createElement('html'));
        $documentElement->appendChild($d->createElement('body'));

        // Just need to run it; mostly covered elsewhere.
        $this->assertTrue($d->documentElement->contains($d->body));
    }


    /**
     * @covers \MensBeam\HTML\DOM\Node::__get_childNodes
     *
     * @covers \MensBeam\HTML\DOM\Collection::__construct
     * @covers \MensBeam\HTML\DOM\Collection::__get_length
     * @covers \MensBeam\HTML\DOM\Collection::count
     * @covers \MensBeam\HTML\DOM\Collection::item
     * @covers \MensBeam\HTML\DOM\Collection::offsetGet
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_lastChild
     * @covers \MensBeam\HTML\DOM\Node::__get_nodeName
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::get
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::has
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::key
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::set
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::getProtectedProperty
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
     * @covers \MensBeam\HTML\DOM\Document::__get_body
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
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::get
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::has
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::key
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::set
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::getProtectedProperty
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
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::get
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::has
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::key
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::set
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::getProtectedProperty
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
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::get
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::has
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::key
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::set
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::getProtectedProperty
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
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::__get_implementation
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DocumentType::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createDocumentType
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_firstChild
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::get
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::has
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::key
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::set
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::getProtectedProperty
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::setProtectedProperties
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
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::__get_implementation
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DocumentType::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createDocumentType
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_lastChild
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::get
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::has
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::key
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::set
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::getProtectedProperty
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::setProtectedProperties
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
     * @covers \MensBeam\HTML\DOM\Comment::__construct
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::__get_implementation
     * @covers \MensBeam\HTML\DOM\Document::__createAttribute
     * @covers \MensBeam\HTML\DOM\Document::createAttribute
     * @covers \MensBeam\HTML\DOM\Document::createAttributeNS
     * @covers \MensBeam\HTML\DOM\Document::createCDATASection
     * @covers \MensBeam\HTML\DOM\Document::createComment
     * @covers \MensBeam\HTML\DOM\Document::createDocumentFragment
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createElementNS
     * @covers \MensBeam\HTML\DOM\Document::createProcessingInstruction
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\Document::validateAndExtract
     * @covers \MensBeam\HTML\DOM\DocumentFragment::__construct
     * @covers \MensBeam\HTML\DOM\DocumentType::__construct
     * @covers \MensBeam\HTML\DOM\DocumentType::__get_name
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createDocumentType
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::cloneInnerNode
     * @covers \MensBeam\HTML\DOM\ProcessingInstruction::__construct
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::get
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::has
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::key
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::set
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::getProtectedProperty
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::setProtectedProperties
     */
    public function testProperty_nodeName() {
        $d = new Document();
        $d2 = new XMLDocument();

        // Node::nodeName on attribute node
        $this->assertSame('href', $d->createAttribute('href')->nodeName);
        // Node::nodeName on attribute node with coerced name
        $this->assertSame('poop💩', $d->createAttribute('poop💩')->nodeName);
        // Node::nodeName on namespaced attribute node
        $this->assertSame('xlink:href', $d->createAttributeNS(Parser::XLINK_NAMESPACE, 'xlink:href')->nodeName);
        // Node::nodeName on namespaced attribute node with coerced name
        $this->assertSame('poop💩:poop💩', $d->createAttributeNS('https://poop💩.poop', 'poop💩:poop💩')->nodeName);

        // Node::nodeName on CDATA section
        $this->assertSame('#cdata-section', $d2->createCDATASection('ook')->nodeName);

        // Node::nodeName on comment
        $this->assertSame('#comment', $d->createComment('comment')->nodeName);

        // Node::nodeName on document
        $this->assertSame('#document', $d->nodeName);

        // Node::nodeName on doctype
        $this->assertSame('html', $d->implementation->createDocumentType('html', '', '')->nodeName);

        // Node::nodeName on document fragment
        $this->assertSame('#document-fragment', $d->createDocumentFragment()->nodeName);

        // Node::nodeName on element
        $this->assertSame('HTML', $d->createElement('html')->nodeName);
        // Node::nodeName on element with coerced name
        $this->assertSame('POOP💩', $d->createElement('poop💩')->nodeName);
        // Node::nodeName on namespaced element
        $this->assertSame('SVG', $d->createElementNS(Parser::SVG_NAMESPACE, 'svg')->nodeName);
        // Node::nodeName on namespaced element with coerced name
        $this->assertSame('POOP💩:POOP💩', $d->createElementNS('https://poop💩.poop', 'poop💩:poop💩')->nodeName);

        // Node::nodeName on processing instruction
        $this->assertSame('ook', $d->createProcessingInstruction('ook', 'eek')->nodeName);
        // Node::nodeName on processing instruction with coerced target
        $this->assertSame('poop💩', $d->createProcessingInstruction('poop💩', 'ook')->nodeName);

        // Node::nodeName on text node
        $this->assertSame('#text', $d->createTextNode('ook')->nodeName);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Node::__get_nodeType
     *
     * @covers \MensBeam\HTML\DOM\Comment::__construct
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::__get_implementation
     * @covers \MensBeam\HTML\DOM\Document::__createAttribute
     * @covers \MensBeam\HTML\DOM\Document::createAttribute
     * @covers \MensBeam\HTML\DOM\Document::createCDATASection
     * @covers \MensBeam\HTML\DOM\Document::createComment
     * @covers \MensBeam\HTML\DOM\Document::createDocumentFragment
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createProcessingInstruction
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DocumentFragment::__construct
     * @covers \MensBeam\HTML\DOM\DocumentType::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createDocumentType
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::cloneInnerNode
     * @covers \MensBeam\HTML\DOM\ProcessingInstruction::__construct
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::get
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::has
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::key
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::set
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::getProtectedProperty
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::setProtectedProperties
     */
    public function testProperty_nodeType() {
        $d = new Document();
        $d2 = new XMLDocument();

        // Node::nodeType on attribute node
        $this->assertSame(Node::ATTRIBUTE_NODE, $d->createAttribute('href')->nodeType);
        // Node::nodeType on CDATA section
        $this->assertSame(Node::CDATA_SECTION_NODE, $d2->createCDATASection('ook')->nodeType);
        // Node::nodeType on comment
        $this->assertSame(Node::COMMENT_NODE, $d->createComment('comment')->nodeType);
        // Node::nodeType on document
        $this->assertSame(Node::DOCUMENT_NODE, $d->nodeType);
        // Node::nodeType on doctype
        $this->assertSame(Node::DOCUMENT_TYPE_NODE, $d->implementation->createDocumentType('html', '', '')->nodeType);
        // Node::nodeType on document fragment
        $this->assertSame(Node::DOCUMENT_FRAGMENT_NODE, $d->createDocumentFragment()->nodeType);
        // Node::nodeType on element
        $this->assertSame(Node::ELEMENT_NODE, $d->createElement('html')->nodeType);
        // Node::nodeType on processing instruction
        $this->assertSame(Node::PROCESSING_INSTRUCTION_NODE, $d->createProcessingInstruction('ook', 'eek')->nodeType);
        // Node::nodeType on text node
        $this->assertSame(Node::TEXT_NODE, $d->createTextNode('ook')->nodeType);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Node::__get_nodeValue
     * @covers \MensBeam\HTML\DOM\Node::__set_nodeValue
     *
     * @covers \MensBeam\HTML\DOM\Attr::__set_value
     * @covers \MensBeam\HTML\DOM\Comment::__construct
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::__get_implementation
     * @covers \MensBeam\HTML\DOM\Document::__createAttribute
     * @covers \MensBeam\HTML\DOM\Document::createAttribute
     * @covers \MensBeam\HTML\DOM\Document::createCDATASection
     * @covers \MensBeam\HTML\DOM\Document::createComment
     * @covers \MensBeam\HTML\DOM\Document::createDocumentFragment
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createProcessingInstruction
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DocumentFragment::__construct
     * @covers \MensBeam\HTML\DOM\DocumentType::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createDocumentType
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::cloneInnerNode
     * @covers \MensBeam\HTML\DOM\ProcessingInstruction::__construct
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::get
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::has
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::key
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::set
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::getProtectedProperty
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::setProtectedProperties
     */
    public function testProperty_nodeValue() {
        $d = new Document();
        $d2 = new XMLDocument();
        $attr = $d->createAttribute('href');
        $attr->value = 'https://poop💩.poop';
        $cdata = $d2->createCDATASection('ook');
        $comment = $d->createComment('comment');
        $element = $d->createElement('html');
        $pi = $d->createProcessingInstruction('ook', 'eek');
        $text = $d->createTextNode('ook');

        // Node::nodeValue on attribute node
        $this->assertSame('https://poop💩.poop', $attr->nodeValue);
        $attr->nodeValue = 'https://ook.com';
        $this->assertSame('https://ook.com', $attr->nodeValue);

        // Node::nodeValue on CDATA section
        $this->assertSame('ook', $cdata->nodeValue);
        $cdata->nodeValue = 'eek';
        $this->assertSame('eek', $cdata->nodeValue);

        // Node::nodeValue on comment
        $this->assertSame('comment', $comment->nodeValue);
        $comment->nodeValue = 'poop💩';
        $this->assertSame('poop💩', $comment->nodeValue);

        // Node::nodeValue on document
        $this->assertnull($d->nodeValue);

        // Node::nodeValue on doctype
        $this->assertNull($d->implementation->createDocumentType('html', '', '')->nodeValue);

        // Node::nodeValue on document fragment
        $this->assertNull($d->createDocumentFragment()->nodeValue);

        // Node::nodeValue on element
        $this->assertNull($element->nodeValue);
        $element->nodeValue = ''; // This should do nothing
        $this->assertNull($element->nodeValue);


        // Node::nodeValue on processing instruction
        $this->assertSame('eek', $pi->nodeValue);
        $pi->nodeValue = 'ook';
        $this->assertSame('ook', $pi->nodeValue);

        // Node::nodeValue on text node
        $this->assertSame('ook', $text->nodeValue);
        $text->nodeValue = 'eek';
        $this->assertSame('eek', $text->nodeValue);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     *
     * @covers \MensBeam\HTML\DOM\Comment::__construct
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::__get_implementation
     * @covers \MensBeam\HTML\DOM\Document::__createAttribute
     * @covers \MensBeam\HTML\DOM\Document::createAttribute
     * @covers \MensBeam\HTML\DOM\Document::createCDATASection
     * @covers \MensBeam\HTML\DOM\Document::createComment
     * @covers \MensBeam\HTML\DOM\Document::createDocumentFragment
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createProcessingInstruction
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DocumentFragment::__construct
     * @covers \MensBeam\HTML\DOM\DocumentType::__construct
     * @covers \MensBeam\HTML\DOM\DocumentType::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createDocumentType
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::cloneInnerNode
     * @covers \MensBeam\HTML\DOM\ProcessingInstruction::__construct
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::get
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::has
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::key
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::set
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::getProtectedProperty
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::setProtectedProperties
     */
    public function testProperty_ownerDocument() {
        $d = new Document();
        $d2 = new XMLDocument();

        // Node::nodeType on attribute node
        $this->assertSame($d, $d->createAttribute('href')->ownerDocument);
        // Node::nodeType on CDATA section
        $this->assertSame($d2, $d2->createCDATASection('ook')->ownerDocument);
        // Node::nodeType on comment
        $this->assertSame($d, $d->createComment('comment')->ownerDocument);
        // Node::nodeType on document
        $this->assertNull($d->ownerDocument);
        // Node::nodeType on doctype
        $this->assertSame($d, $d->implementation->createDocumentType('html', '', '')->ownerDocument);
        // Node::nodeType on document fragment
        $this->assertSame($d, $d->createDocumentFragment()->ownerDocument);
        // Node::nodeType on element
        $this->assertSame($d, $d->createElement('html')->ownerDocument);
        // Node::nodeType on processing instruction
        $this->assertSame($d, $d->createProcessingInstruction('ook', 'eek')->ownerDocument);
        // Node::nodeType on text node
        $this->assertSame($d, $d->createTextNode('ook')->ownerDocument);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Node::__get_parentElement
     * @covers \MensBeam\HTML\DOM\Node::__get_parentNode
     *
     * @covers \MensBeam\HTML\DOM\Attr::__set_value
     * @covers \MensBeam\HTML\DOM\Comment::__construct
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::__get_implementation
     * @covers \MensBeam\HTML\DOM\Document::__createAttribute
     * @covers \MensBeam\HTML\DOM\Document::createAttribute
     * @covers \MensBeam\HTML\DOM\Document::createCDATASection
     * @covers \MensBeam\HTML\DOM\Document::createComment
     * @covers \MensBeam\HTML\DOM\Document::createDocumentFragment
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createProcessingInstruction
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DocumentFragment::__construct
     * @covers \MensBeam\HTML\DOM\DocumentType::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createDocumentType
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNode
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\ProcessingInstruction::__construct
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::get
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::has
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::key
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::set
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::getProtectedProperty
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::setProtectedProperties
     */
    public function testProperty_parentElement() {
        $d = new Document();
        $doctype = $d->appendChild($d->implementation->createDocumentType('html', '', ''));
        $documentElement = $d->appendChild($d->createElement('html'));
        $documentElement->appendChild($d->createElement('body'));
        $body = $d->body;
        $attr = $d->createAttribute('href');
        $attr->value = 'https://poop💩.poop';
        $body->setAttributeNode($attr);
        $comment = $body->appendChild($d->createComment('ook'));
        $pi = $body->appendChild($d->createProcessingInstruction('ook', 'eek'));
        $text = $body->appendChild($d->createTextNode('ook'));

        $d2 = new XMLDocument();
        $xmlElement = $d2->appendChild($d2->createElement('ook'));
        $cdata = $xmlElement->appendChild($d2->createCDATASection('ook'));

        // Node::parentElement on attribute node
        $this->assertSame($body, $attr->parentElement);
        // Node::parentElement on CDATA section
        $this->assertSame($xmlElement, $cdata->parentElement);
        // Node::parentElement on comment
        $this->assertSame($body, $comment->parentElement);
        // Node::parentElement on document
        $this->assertNull($d->parentElement);
        // Node::parentElement on doctype
        $this->assertNull($doctype->parentElement);
        // Node::parentNode on doctype
        $this->assertSame($d, $doctype->parentNode);
        // Node::parentElement on document fragment
        $this->assertNull($d->createDocumentFragment()->parentElement);
        // Node::parentElement on element
        $this->assertSame($documentElement, $body->parentElement);
        // Node::parentElement on processing instruction
        $this->assertSame($body, $pi->parentElement);
        // Node::parentElement on text node
        $this->assertSame($body, $text->parentElement);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Node::__get_textContent
     * @covers \MensBeam\HTML\DOM\Node::__set_textContent
     *
     * @covers \MensBeam\HTML\DOM\Attr::__set_value
     * @covers \MensBeam\HTML\DOM\Collection::__construct
     * @covers \MensBeam\HTML\DOM\Collection::__get_length
     * @covers \MensBeam\HTML\DOM\Collection::count
     * @covers \MensBeam\HTML\DOM\Comment::__construct
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::__get_implementation
     * @covers \MensBeam\HTML\DOM\Document::__createAttribute
     * @covers \MensBeam\HTML\DOM\Document::createAttribute
     * @covers \MensBeam\HTML\DOM\Document::createCDATASection
     * @covers \MensBeam\HTML\DOM\Document::createComment
     * @covers \MensBeam\HTML\DOM\Document::createDocumentFragment
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createProcessingInstruction
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DocumentFragment::__construct
     * @covers \MensBeam\HTML\DOM\DocumentType::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createDocumentType
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNode
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_childNodes
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\ProcessingInstruction::__construct
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__construct
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::get
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::has
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::key
     * @covers \MensBeam\HTML\DOM\InnerNode\NodeMap::set
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::getProtectedProperty
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::setProtectedProperties
     */
    public function testProperty_textContent() {
        $d = new Document();
        $doctype = $d->appendChild($d->implementation->createDocumentType('html', '', ''));
        $documentElement = $d->appendChild($d->createElement('html'));
        $documentElement->appendChild($d->createElement('body'));
        $body = $d->body;
        $attr = $d->createAttribute('href');
        $attr->value = 'https://poop💩.poop';
        $body->setAttributeNode($attr);
        $comment = $body->appendChild($d->createComment('ook'));
        $pi = $body->appendChild($d->createProcessingInstruction('ook', 'eek'));
        $text = $body->appendChild($d->createTextNode('ook'));
        $frag = $d->createDocumentFragment();
        $frag->appendChild($d->createTextNode('ook'));
        $frag->appendChild($d->createElement('br'));

        $d2 = new XMLDocument();
        $cdata = $d2->createCDATASection('ook');

        // Node::textContent on attribute node
        $this->assertSame('https://poop💩.poop', $attr->textContent);
        $attr->textContent = 'https://ook🐒ook';
        $this->assertSame('https://ook🐒ook', $attr->textContent);

        // Node::textContent on CDATA section
        $this->assertSame('ook', $cdata->textContent);
        $cdata->textContent = 'eek';
        $this->assertSame('eek', $cdata->textContent);

        // Node::textContent on comment
        $this->assertSame('ook', $comment->textContent);
        $comment->textContent = 'eek';
        $this->assertSame('eek', $comment->textContent);

        // Node::textContent on document
        $this->assertNull($d->textContent);
        $d->textContent = '';
        $this->assertNull($d->textContent);

        // Node::textContent on doctype
        $this->assertNull($doctype->textContent);
        $doctype->textContent = '';
        $this->assertNull($doctype->textContent);

        // Node::textContent on document fragment
        $this->assertSame('ook', $frag->textContent);
        $frag->textContent = 'eek';
        $this->assertSame('eek', $frag->textContent);
        $this->assertEquals(1, $frag->childNodes->length);

        // Node::textContent on element
        $this->assertSame('ook', $body->textContent);
        $body->textContent = 'eek';
        $this->assertSame('eek', $body->textContent);
        $this->assertEquals(1, $body->childNodes->length);

        // Node::textContent on processing instruction
        $this->assertSame('eek', $pi->textContent);
        $pi->textContent = 'ook';
        $this->assertSame('ook', $pi->textContent);

        // Node::textContent on text node
        $this->assertSame('ook', $text->textContent);
        $text->textContent = 'eek';
        $this->assertSame('eek', $text->textContent);
    }
}