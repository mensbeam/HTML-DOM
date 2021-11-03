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
use MensBeam\HTML\Parser;


/** @covers \MensBeam\HTML\DOM\Document */
class TestDocument extends \PHPUnit\Framework\TestCase {
    public function testMethod_adoptNode() {
        $d = new Document();
        $documentElement = $d->appendChild($d->createElement('html'));
        $body = $documentElement->appendChild($d->createElement('body'));
        $template = $body->appendChild($d->createElement('template'));
        $d2 = new Document();

        $d2->adoptNode($documentElement);
        $this->assertSame($d2, $documentElement->ownerDocument);

        $d2->adoptNode($template->content);
        $this->assertSame($d, $template->content->ownerDocument);
    }


    public function testMethod_adoptNode_errors() {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::NOT_SUPPORTED);
        $d = new Document();
        $d2 = new Document();
        $d2->adoptNode($d);
    }


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
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::cloneInnerNode
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__construct
     */
    public function testProperty_charset() {
        $d = new Document('<!DOCTYPE html><html><head><meta charset="gb2312"></head></html>');
        $this->assertSame('GBK', $d->charset);
        $this->assertSame('GBK', $d->inputEncoding);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__get_doctype
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_implementation
     * @covers \MensBeam\HTML\DOM\DocumentType::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createDocumentType
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
     * @covers \MensBeam\HTML\DOM\InnerNode\Reflection::setProtectedProperties
     */
    public function testProperty_doctype() {
        $d = new Document();
        $this->assertNull($d->doctype);

        $doctype = $d->appendChild($d->implementation->createDocumentType('html', '', ''));
        $this->assertSame($doctype, $d->doctype);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__get_documentURI
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\Document::loadFile
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::cloneInnerNode
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\InnerNode\Document::__construct
     */
    public function testProperty_documentURI() {
        $d = new Document();
        $d->loadFile('https://google.com');
        $this->assertSame('https://google.com', $d->documentURI);
    }
}