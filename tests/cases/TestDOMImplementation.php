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
    DOMImplementation,
    Node
};


/** @covers \MensBeam\HTML\DOM\DOMImplementation */
class TestDOMImplementation extends \PHPUnit\Framework\TestCase {
    /**
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createDocument
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_contentType
     * @covers \MensBeam\HTML\DOM\Document::__get_doctype
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::createElementNS
     * @covers \MensBeam\HTML\DOM\DocumentOrElement::validateAndExtract
     * @covers \MensBeam\HTML\DOM\DocumentType::__construct
     * @covers \MensBeam\HTML\DOM\DocumentType::__get_name
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createDocumentType
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_localName
     * @covers \MensBeam\HTML\DOM\Element::__get_namespaceURI
     * @covers \MensBeam\HTML\DOM\Element::__get_prefix
     * @covers \MensBeam\HTML\DOM\Element::__get_tagName
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::__get_nodeName
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::setProtectedProperties
     */
    public function testMethod_createDocument(): void {
        $di = new DOMImplementation();
        $d = $di->createDocument(null, 'ook', $di->createDocumentType('ook', 'eek', 'ack'));
        $this->assertNull($d->documentElement->namespaceURI);
        $this->assertSame('ook', $d->documentElement->tagName);
        $this->assertSame('ook', $d->doctype->nodeName);
        $this->assertSame('application/xml', $d->contentType);

        $d = $di->createDocument(Node::HTML_NAMESPACE, 'html', $di->createDocumentType('html', '', ''));
        $this->assertSame(Node::HTML_NAMESPACE, $d->documentElement->namespaceURI);
        $this->assertSame('html', $d->documentElement->tagName);
        $this->assertSame('html', $d->doctype->nodeName);
        $this->assertSame('application/xhtml+xml', $d->contentType);

        $d = $di->createDocument(Node::SVG_NAMESPACE, 'svg', null);
        $this->assertSame(Node::SVG_NAMESPACE, $d->documentElement->namespaceURI);
        $this->assertSame('svg', $d->documentElement->tagName);
        $this->assertNull($d->doctype);
        $this->assertSame('image/svg+xml', $d->contentType);
    }


    /**
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createDocumentType
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
    public function testMethod_createDocumentType__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::INVALID_CHARACTER);

        $di = new DOMImplementation();
        $di->createDocumentType('fail fail', 'fail', 'fail');
    }


    /**
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createHTMLDocument
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_contentType
     * @covers \MensBeam\HTML\DOM\Document::__get_doctype
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::__get_implementation
     * @covers \MensBeam\HTML\DOM\Document::__get_title
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DocumentType::__construct
     * @covers \MensBeam\HTML\DOM\DocumentType::__get_name
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createDocumentType
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_localName
     * @covers \MensBeam\HTML\DOM\Element::__get_namespaceURI
     * @covers \MensBeam\HTML\DOM\Element::__get_prefix
     * @covers \MensBeam\HTML\DOM\Element::__get_tagName
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::__get_nodeName
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::setProtectedProperties
     */
    public function testMethod_createHTMLDocument(): void {
        $di = new DOMImplementation();
        $d = $di->createHTMLDocument('ook');
        $this->assertSame(Node::HTML_NAMESPACE, $d->documentElement->namespaceURI);
        $this->assertSame('html', $d->documentElement->tagName);
        $this->assertSame('html', $d->doctype->nodeName);
        $this->assertSame('text/html', $d->contentType);
        $this->assertSame('ook', $d->title);
    }


    /**
     * @covers \MensBeam\HTML\DOM\DOMImplementation::hasFeature
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
    public function testMethod_hasFeature(): void {
        $di = new DOMImplementation();
        $this->assertTrue($di->hasFeature());
    }
}