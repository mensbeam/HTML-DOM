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
    Node,
    XMLDocument
};
use MensBeam\HTML\Parser,
    org\bovigo\vfs\vfsStream;


/** @covers \MensBeam\HTML\DOM\Document */
class TestDocument extends \PHPUnit\Framework\TestCase {
    public function testMethod_adoptNode(): void {
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


    public function testMethod_adoptNode_errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::NOT_SUPPORTED);
        $d = new Document();
        $d2 = new Document();
        $d2->adoptNode($d);
    }


    public function testMethod_createAttribute_errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::INVALID_CHARACTER);
        $d = new Document();
        $d->createAttribute('this will fail');
    }


    public function provideMethod_createCDATASection_errors(): iterable {
        return [
            [ function () {
                $d = new Document();
                $d->createCDATASection('ook');
            }, DOMException::NOT_SUPPORTED ],
            [ function () {
                $d = new XMLDocument();
                $d->createCDATASection('ook]]>');
            }, DOMException::INVALID_CHARACTER ],
        ];
    }

    /** @dataProvider provideMethod_createCDATASection_errors */
    public function testMethod_createCDATASection_errors(\Closure $closure, int $errorCode): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode($errorCode);
        $closure();
    }


    public function testMethod_createElement_errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::INVALID_CHARACTER);
        $d = new Document();
        $d->createElement('this will fail');
    }


    public function testMethod_importNode_errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::NOT_SUPPORTED);
        $d = new Document();
        $d->importNode(new Document());
    }


    public function testMethod_load_errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::NO_MODIFICATION_ALLOWED);
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->load('this will fail');
    }


    public function testMethod_loadFile(): void {
        $d = new Document();

        $vfs = vfsStream::setup('DOM', 0777, [ 'test.html' => <<<HTML
        <!DOCTYPE html>
        <html>
         <head>
          <meta charset="ISO-2022-JP">
          <title>Ook</title>
         </head>
        </html>
        HTML ]);
        $f = $vfs->url() . '/test.html';

        // Test loading of virtual file
        $d->loadFile($f);
        $this->assertSame('ISO-2022-JP', $d->charset);
        $this->assertStringStartsWith('vfs://', $d->URL);

        // Test loading of local file
        $d = new Document();
        $d->loadFile(dirname(__FILE__) . '/../test.html', 'UTF-8');
        $this->assertSame('ISO-2022-JP', $d->charset);
        $this->assertStringStartsWith('file://', $d->URL);
    }


    public function testMethod_loadFile_errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::FILE_NOT_FOUND);
        $d = new Document();
        $d->loadFile('fail.html');
    }


    public function testMethod_serialize_errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::WRONG_DOCUMENT);
        $d = new XMLDocument();
        $d2 = new Document();
        $d2->serialize($d->createTextNode('ook'));
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