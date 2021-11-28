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
    Element,
    Node,
    Text,
    XMLDocument
};
use org\bovigo\vfs\vfsStream;


/** @covers \MensBeam\HTML\DOM\Document */
class TestDocument extends \PHPUnit\Framework\TestCase {
    /**
     * @covers \MensBeam\HTML\DOM\Document::adoptNode
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createDocumentFragment
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::importNode
     * @covers \MensBeam\HTML\DOM\DocumentFragment::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\HTMLTemplateElement::__construct
     * @covers \MensBeam\HTML\DOM\HTMLTemplateElement::__get_content
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::__get_parentNode
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::appendChildInner
     * @covers \MensBeam\HTML\DOM\Node::cloneInnerNode
     * @covers \MensBeam\HTML\DOM\Node::cloneWrapperNode
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\Node::removeChild
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::delete
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::setProtectedProperties
     */
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


    /**
     * @covers \MensBeam\HTML\DOM\Document::adoptNode
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
    public function testMethod_adoptNode__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::NOT_SUPPORTED);
        $d = new Document();
        $d2 = new Document();
        $d2->adoptNode($d);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::createAttribute
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
    public function testMethod_createAttribute__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::INVALID_CHARACTER);
        $d = new Document();
        $d->createAttribute('this will fail');
    }


    public function provideMethod_createCDATASection__errors(): iterable {
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

    /**
     * @dataProvider provideMethod_createCDATASection__errors
     * @covers \MensBeam\HTML\DOM\Document::createCDATASection
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
    public function testMethod_createCDATASection__errors(\Closure $closure, int $errorCode): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode($errorCode);
        $closure();
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::createElement
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
    public function testMethod_createElement__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::INVALID_CHARACTER);
        $d = new Document();
        $d->createElement('this will fail');
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::importNode
     *
     * @covers \MensBeam\HTML\DOM\CharacterData::__get_data
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createCDATASection
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_firstChild
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::cloneInnerNode
     * @covers \MensBeam\HTML\DOM\Node::cloneWrapperNode
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
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
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     */
    public function testMethod_importNode(): void {
        $d = new Document();
        $d2 = new XMLDocument();
        $d3 = new \DOMDocument();

        // Test importing of PHP DOM node
        $div = $d3->createElement('div');
        $div = $d->importNode($div);
        $this->assertTrue($div instanceof Element);

        // Test importing of CDATA section node
        $cdata = $d2->createCDATASection('ook');
        $cdata = $d->importNode($cdata);
        $this->assertSame(Text::class, $cdata::class);

        // Test importing of element containing CDATA section node
        $div = $d2->createElement('div');
        $div->appendChild($d2->createCDATASection('ook'));
        $div = $d->importNode($div, true);
        $this->assertSame(Text::class, $div->firstChild::class);
    }


    public function provideMethod_importNode__errors(): iterable {
        return [
            [ function () {
                $d = new Document();
                $d->importNode(new Document());
            } ],
            [ function () {
                $d = new Document();
                $d->importNode(new \DOMDocument());
            } ],
            [ function () {
                $d = new Document();
                $d2 = new class extends \DOMDocument {};
                $d2->createTextNode('fail');
                $d->importNode($d2);
            } ],
            [ function () {
                $d = new Document();
                $d2 = new \DOMDocument();
                $d2->createEntityReference('nbsp');
                $d->importNode($d2);
            } ],
        ];
    }

    /**
     * @dataProvider provideMethod_importNode__errors
     * @covers \MensBeam\HTML\DOM\Document::importNode
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
    public function testMethod_importNode__errors(\Closure $closure): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::NOT_SUPPORTED);
        $closure();
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::load
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
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
    public function testMethod_load__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::NO_MODIFICATION_ALLOWED);
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->load('this will fail');
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::loadFile
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_charset
     * @covers \MensBeam\HTML\DOM\Document::__get_URL
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
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


    /**
     * @covers \MensBeam\HTML\DOM\Document::loadFile
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
    public function testMethod_loadFile__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::FILE_NOT_FOUND);
        $d = new Document();
        $d->loadFile('fail.html');
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::serialize
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testMethod_serialize__errors(): void {
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
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
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
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
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
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::setProtectedProperties
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
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
    public function testProperty_documentURI() {
        $d = new Document();
        $d->loadFile('https://google.com');
        $this->assertSame('https://google.com', $d->documentURI);
    }


    public function testProperty_title() {
        $d = new Document();
        $this->assertSame('', $d->title);
        $d->title = 'fail';
        $this->assertSame('', $d->title);

        $d = (new DOMImplementation)->createDocument(Node::SVG_NAMESPACE, 'svg');
        $this->assertSame('', $d->title);

        $d->title = 'Ook';
        $this->assertSame('Ook', $d->title);
        $d->title = '   Ee  k  ';
        $this->assertSame('Ee k', $d->title);

        $d = new Document();
        $de = $d->appendChild($d->createElement('html'));
        $d->title = 'Ook';
        $this->assertSame('', $d->title);

        $de->appendChild($d->createElement('head'));
        $d->title = 'Ook';
        $this->assertSame('Ook', $d->title);
        $d->title = 'Eek';
        $this->assertSame('Eek', $d->title);
    }
}