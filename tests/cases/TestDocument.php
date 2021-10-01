<?php
/** @license MIT
 * Copyright 2017 , Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\TestCase;

use MensBeam\HTML\DOM\{
    Document,
    DOMException,
    Element,
    Exception,
    HTMLTemplateElement
};
use MensBeam\HTML\Parser;


class TestDocument extends \PHPUnit\Framework\TestCase {
    public function provideAttributeNodes(): iterable {
        return [
            [ 'test',      'test' ],
            [ 'TEST',      'test' ],
            [ 'test:test', 'testU00003Atest' ],
            [ 'TEST:TEST', 'testU00003Atest' ]
        ];
    }

    /**
     * @dataProvider provideAttributeNodes
     * @covers \MensBeam\HTML\DOM\Document::createAttribute
     */
    public function testAttributeNodeCreation(string $nameIn, string $local): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $a = $d->createAttribute($nameIn);
        $this->assertSame($local, $a->localName);
    }


    public function provideAttributeNodesNS(): iterable {
        return [
            [ 'fake_ns', 'test',      'test', '' ],
            [ 'fake_ns', 'test:test', 'test', 'test' ],
            [ 'fake_ns', 'TEST:TEST', 'TEST', 'TEST' ]
        ];
    }

    /**
     * @dataProvider provideAttributeNodesNS
     * @covers \MensBeam\HTML\DOM\Document::createAttributeNS
     */
    public function testAttributeNodeNSCreation(?string $nsIn, string $nameIn, string $local, string $prefix): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $a = $d->createAttributeNS($nsIn, $nameIn);
        $this->assertSame($local, $a->localName);
        $this->assertSame($nsIn, $a->namespaceURI);
        $this->assertSame($prefix, $a->prefix);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::loadDOM
     * @covers \MensBeam\HTML\DOM\Document::loadHTML
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
    }


    public function provideElements(): iterable {
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
     * @dataProvider provideElements
     * @covers \MensBeam\HTML\DOM\Document::createElement
     */
    public function testElementCreation(string $localIn, string $localOut, string $class): void {
        $d = new Document;
        $n = $d->createElement($localIn);
        $this->assertInstanceOf($class, $n);
        $this->assertNotNull($n->ownerDocument);
        $this->assertSame($localOut, $n->localName);
    }


    public function provideElementsNS(): iterable {
        return [
            // HTML element with a null namespace
            [ null,                               null,                  'div',         'div',      Element::class ],
            // Template element with a null namespace
            [ null,                               null,                  'template',    'template', HTMLTemplateElement::class ],
            // Template element with a null namespace and uppercase name
            [ null,                               null,                  'TEMPLATE',    'TEMPLATE', HTMLTemplateElement::class ],
            // Template element
            [ Parser::HTML_NAMESPACE,             Parser::HTML_NAMESPACE, 'template',    'template', HTMLTemplateElement::class ],
            // SVG element with SVG namespace
            [ Parser::SVG_NAMESPACE,              Parser::SVG_NAMESPACE, 'svg',         'svg',      Element::class ],
            // SVG element with SVG namespace and uppercase local name
            [ Parser::SVG_NAMESPACE,              Parser::SVG_NAMESPACE, 'SVG',         'SVG',      Element::class ]
        ];
    }

    /**
     * @dataProvider provideElementsNS
     * @covers \MensBeam\HTML\DOM\Document::createElementNS
     */
    public function testElementCreationNS(?string $nsIn, ?string $nsOut, string $localIn, string $localOut, string $class): void {
        $d = new Document;
        $n = $d->createElementNS($nsIn, $localIn);
        $this->assertInstanceOf($class, $n);
        $this->assertNotNull($n->ownerDocument);
        $this->assertSame($nsOut, $n->namespaceURI);
        $this->assertSame($localOut, $n->localName);
    }
}
