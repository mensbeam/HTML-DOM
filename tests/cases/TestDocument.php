<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\TestCase;

use MensBeam\HTML\DOM\{
    Document,
    DOMException,
    Element,
    ElementMap,
    HTMLTemplateElement
};
use MensBeam\HTML\Parser,
    org\bovigo\vfs\vfsStream;


/** @covers \MensBeam\HTML\DOM\Document */
class TestDocument extends \PHPUnit\Framework\TestCase {
    public function provideAttributeNodeCreation(): iterable {
        return [
            [ 'test',      'test' ],
            [ 'TEST',      'test' ],
            [ 'test:test', 'testU00003Atest' ],
            [ 'TEST:TEST', 'testU00003Atest' ]
        ];
    }

    /**
     * @dataProvider provideAttributeNodeCreation
     * @covers \MensBeam\HTML\DOM\Document::createAttribute
     */
    public function testAttributeNodeCreation(string $nameIn, string $local): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $a = $d->createAttribute($nameIn);
        $this->assertSame($local, $a->localName);
    }


    public function provideAttributeNodeNSCreation(): iterable {
        return [
            [ 'fake_ns', 'test',      'test', '' ],
            [ 'fake_ns', 'test:test', 'test', 'test' ],
            [ 'fake_ns', 'TEST:TEST', 'TEST', 'TEST' ]
        ];
    }

    /**
     * @dataProvider provideAttributeNodeNSCreation
     * @covers \MensBeam\HTML\DOM\Document::createAttributeNS
     * @covers \MensBeam\HTML\DOM\Document::validateAndExtract
     */
    public function testAttributeNodeNSCreation(?string $nsIn, string $nameIn, string $local, string $prefix): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $a = $d->createAttributeNS($nsIn, $nameIn);
        $this->assertSame($local, $a->localName);
        $this->assertSame($nsIn, $a->namespaceURI);
        $this->assertSame($prefix, $a->prefix);
    }


    public function provideDisabledMethods(): iterable {
        return [
            [ 'loadXML', 'ook' ],
            [ 'saveXML', null ],
            [ 'validate', null ],
            [ 'xinclude', null ],
        ];
    }

    /**
     * @dataProvider provideDisabledMethods
     * @covers \MensBeam\HTML\DOM\Document::loadXML
     * @covers \MensBeam\HTML\DOM\Document::saveXML
     * @covers \MensBeam\HTML\DOM\Document::validate
     * @covers \MensBeam\HTML\DOM\Document::xinclude
     */
    public function testDisabledMethods(string $methodName, ?string $argument): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::NOT_SUPPORTED);
        $d = new Document();
        $d->$methodName($argument);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\Document::loadDOM
     * @covers \MensBeam\HTML\DOM\Document::loadHTML
     * @covers \MensBeam\HTML\DOM\Document::loadHTMLFile
     * @covers \MensBeam\HTML\DOM\Document::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\Document::__get_quirksMode
     */
    public function testDocumentCreation(): void {
        // Test null source
        $d = new Document();
        $this->assertSame('MensBeam\HTML\DOM\Document', $d::class);
        $this->assertSame(null, $d->firstChild);

        // Test string source
        $d = new Document('<html><body>Ook!</body></html>');
        $this->assertSame(Parser::QUIRKS_MODE, $d->quirksMode);

        // Test DOM source
        $d = new \DOMDocument();
        $d->appendChild($d->createElement('html'));
        $d = new Document($d);
        $this->assertSame('MensBeam\HTML\DOM\Element', $d->firstChild::class);
        $this->assertSame('html', $d->firstChild->nodeName);

        // Test file source
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

        $d = new Document();
        $d->load($f);
        $this->assertNotNull($d->documentElement);
        $this->assertSame('ISO-2022-JP', $d->documentEncoding);

        $d = new Document();
        $d->loadHTMLFile($f, null, 'UTF-8');
        $this->assertSame('UTF-8', $d->documentEncoding);
    }


    public function provideElementCreation(): iterable {
        return [
            // HTML element
            [ 'div',         'div',      Element::class ],
            // HTML element and uppercase local name
            [ 'DIV',         'div',      Element::class ],
            // Template element
            [ 'template',    'template', HTMLTemplateElement::class ],
            // Template element and uppercase local name
            [ 'TEMPLATE',    'template', HTMLTemplateElement::class ]
        ];
    }

    /**
     * @dataProvider provideElementCreation
     * @covers \MensBeam\HTML\DOM\Document::createElement
     */
    public function testElementCreation(string $localIn, string $localOut, string $class): void {
        $d = new Document;
        $n = $d->createElement($localIn);
        $this->assertInstanceOf($class, $n);
        $this->assertNotNull($n->ownerDocument);
        $this->assertSame($localOut, $n->localName);
    }


    public function provideElementCreationNS(): iterable {
        return [
            // HTML element with a null namespace
            [ null,                   null,                   'div',      'div',      Element::class ],
            // Template element with a null namespace
            [ null,                   null,                   'template', 'template', HTMLTemplateElement::class ],
            // Template element with a null namespace and uppercase name
            [ null,                   null,                   'TEMPLATE', 'TEMPLATE', HTMLTemplateElement::class ],
            // Template element
            [ Parser::HTML_NAMESPACE, Parser::HTML_NAMESPACE, 'template', 'template', HTMLTemplateElement::class ],
            // SVG element with SVG namespace
            [ Parser::SVG_NAMESPACE,  Parser::SVG_NAMESPACE,  'svg',      'svg',      Element::class ],
            // SVG element with SVG namespace and uppercase local name
            [ Parser::SVG_NAMESPACE,  Parser::SVG_NAMESPACE,  'SVG',      'SVG',      Element::class ]
        ];
    }

    /**
     * @dataProvider provideElementCreationNS
     * @covers \MensBeam\HTML\DOM\Document::createElementNS
     * @covers \MensBeam\HTML\DOM\Document::validateAndExtract
     */
    public function testElementCreationNS(?string $nsIn, ?string $nsOut, string $localIn, string $localOut, string $class): void {
        $d = new Document();
        $n = $d->createElementNS($nsIn, $localIn);
        $this->assertInstanceOf($class, $n);
        $this->assertNotNull($n->ownerDocument);
        $this->assertSame($nsOut, $n->namespaceURI);
        $this->assertSame($localOut, $n->localName);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::save
     * @covers \MensBeam\HTML\DOM\Document::saveHTMLFile
     */
    public function testFileSaving(): void {
        $vfs = vfsStream::setup('DOM', 0777);
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $path = $vfs->url() . '/test.html';
        $d->save($path);
        $this->assertSame('<html></html>', file_get_contents($path));
    }


    /** @covers \MensBeam\HTML\DOM\Document::importNode */
    public function testImportingNodes() {
        $d = new Document();
        $t = $d->createElement('template');

        $d2 = new Document();
        $t2 = $d2->importNode($t, true);
        $this->assertFalse($t2->ownerDocument->isSameNode($t->ownerDocument));
        $this->assertSame($t2::class, $t::class);

        $d = new \DOMDocument();
        $t = $d->createElement('template');
        $this->assertSame($t::class, 'DOMElement');

        $d2 = new Document();
        $t2 = $d2->importNode($t, true);
        $this->assertSame($t2::class, str_replace(\DIRECTORY_SEPARATOR, '\\', dirname(str_replace('\\', \DIRECTORY_SEPARATOR, __NAMESPACE__))) . '\HTMLTemplateElement');
    }


    /** @covers \MensBeam\HTML\DOM\Document::__set_body */
    public function testPropertySetBody(): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $b = $d->createElement('body');
        $d->body = $b;
        $this->assertSame('body', $d->body->nodeName);

        $b = $d->createElement('body');
        $b->appendChild($d->createTextNode('Ook'));
        $d->body = $b;
        $this->assertSame('Ook', $d->body->firstChild->data);
    }

    /** @covers \MensBeam\HTML\DOM\Document::__set_body */
    public function testPropertySetBodyFailure(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::HIERARCHY_REQUEST_ERROR);
        $d = new Document();
        $b = $d->createElement('body');
        $d->body = $b;
    }


    /** @covers \MensBeam\HTML\DOM\Document::__get_documentEncoding */
    public function testPropertyGetDocumentEncoding(): void {
        $d = new Document(null, 'UTF-8');
        $this->assertSame('UTF-8', $d->documentEncoding);

        $d = new Document('<!DOCTYPE html><html><head><meta charset="GB18030"></head></html>');
        $this->assertSame('gb18030', $d->documentEncoding);
    }


    public function providePropertyGetQuirksMode(): iterable {
        return [
            // Empty document
            [ null,                           Parser::NO_QUIRKS_MODE ],
            // Document without doctype
            [ '<html></html>',                Parser::QUIRKS_MODE ],
            // Document with doctype
            [ '<!DOCTYPE html><html></html>', Parser::NO_QUIRKS_MODE ]
        ];
    }

    /**
     * @dataProvider providePropertyGetQuirksMode
     * @covers \MensBeam\HTML\DOM\Document::__get_quirksMode
     */
    public function testPropertyGetQuirksMode(?string $html, int $quirksMode): void {
        $d = new Document($html);
        $this->assertSame($quirksMode, $d->quirksMode);
    }


    /** @covers \MensBeam\HTML\DOM\Document::__get_xpath */
    public function testPropertyGetXPath(): void {
        $d = new Document();
        $this->assertSame('DOMXPath', $d->xpath::class);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__destruct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\ElementMap::add
     * @covers \MensBeam\HTML\DOM\ElementMap::delete
     * @covers \MensBeam\HTML\DOM\ElementMap::destroy
     * @covers \MensBeam\HTML\DOM\ElementMap::has
     * @covers \MensBeam\HTML\DOM\ElementMap::index
     */
    public function testTemplateElementReferences(): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $t = $d->createElement('template');
        $this->assertFalse(ElementMap::has($t));
        $d->body->appendChild($t);
        $this->assertTrue(ElementMap::has($t));
        $d->__destruct();
        $this->assertFalse(ElementMap::has($t));

        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $t = $d->importNode($t);
        $this->assertFalse(ElementMap::has($t));
        $d->body->appendChild($t);
        $this->assertTrue(ElementMap::has($t));
        $d->body->removeChild($t);
        $this->assertFalse(ElementMap::has($t));
    }
}