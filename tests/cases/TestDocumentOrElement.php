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
    Node,
    Text,
    XMLDocument
};
use MensBeam\HTML\Parser;


/** @covers \MensBeam\HTML\DOM\DocumentOrElement */
class TestDocumentOrElement extends \PHPUnit\Framework\TestCase {
    public function testMethod_getElementsByClassName() {
        $d = new Document('<!DOCTYPE html><html><body><div class="ook eek"><span class="ook eek"><i class="ookeek eek"></i></span></div></body></html>');

        // Empty class
        $ook = $d->getElementsByClassName('');
        $this->assertEquals(0, $ook->length);

        // Whitespace
        $ook = $d->getElementsByClassName('     ');
        $this->assertEquals(0, $ook->length);

        // Document context node
        $ook = $d->getElementsByClassName('ook');
        $this->assertEquals(2, $ook->length);

        // Document context node with additional whitespace
        $ook = $d->getElementsByClassName('     ook ');
        $this->assertEquals(2, $ook->length);

        // Element context node
        $div = $ook[0];
        $ook = $div->getElementsByClassName('ook');
        $this->assertSame('DIV', $div->nodeName);
        $this->assertEquals(1, $ook->length);
        $this->assertSame('SPAN', $ook[0]->nodeName);

        // Multiple classes
        $ook = $d->getElementsByClassName('ook eek');
        $this->assertEquals(2, $ook->length);
    }


    public function testMethod_getElementsByTagName() {
        $d = new Document('<!DOCTYPE html><html><body><div><div><span></span></div></div><div></div><span></span><span></span><svg></svg></body></html>');

        // Document context
        $div = $d->getElementsByTagName('div');
        $this->assertEquals(3, $div->length);

        // Element context
        $div = $div[1];
        $span = $div->getElementsByTagName('span');
        $this->assertEquals(1, $span->length);

        // Wildcard
        $all = $d->getElementsByTagName('*');
        $this->assertEquals(10, $all->length);

        // XML Document
        $d = new XMLDocument();
        $ook = $d->appendChild($d->createElement('ook'));
        for ($i = 0; $i < 9; $i++) {
            $ook->appendChild($d->createElement('ook'));
        }
        $ook = $d->getElementsByTagName('ook');
        $this->assertEquals(10, $ook->length);
    }


    public function testMethod_getElementsByTagNameNS() {
        $d = new Document('<!DOCTYPE html><html><body><div><div><span></span></div></div><div></div><span></span><span></span><svg></svg></body></html>');
        $svg = $d->body->lastChild;
        $svg->appendChild($d->createElementNS(Parser::SVG_NAMESPACE, 'div'));

        // HTML namespace
        $div = $d->getElementsByTagNameNS(Parser::HTML_NAMESPACE, 'div');
        $this->assertEquals(3, $div->length);

        // Null namespace
        $div = $d->getElementsByTagNameNS(null, 'div');
        $this->assertEquals(0, $div->length);

        // Empty string namespace
        $div = $d->getElementsByTagNameNS('', 'div');
        $this->assertEquals(0, $div->length);

        // Wildcard namespace and local name
        $all = $d->getElementsByTagNameNS('*', '*');
        $this->assertEquals(11, $all->length);

        // Wildcard namespace
        $div = $d->getElementsByTagNameNS('*', 'div');
        $this->assertEquals(4, $div->length);

        // Wildcard local name
        $svg = $d->getElementsByTagNameNS(Parser::SVG_NAMESPACE, '*');
        $this->assertEquals(2, $svg->length);
    }


    public function provideMethod_validateAndExtract_errors(): iterable {
        return [
            [ function() {
                $d = new Document();
                $d->createElementNS(Parser::HTML_NAMESPACE, 'this will fail');
            }, DOMException::INVALID_CHARACTER ],
            [ function() {
                $d = new Document();
                $d->createAttributeNS(Parser::HTML_NAMESPACE, 'xmlns');
            }, DOMException::NAMESPACE_ERROR ]
        ];
    }

    /**
     * @dataProvider provideMethod_validateAndExtract_errors
     */
    public function testMethod_validateAndExtract_errors(\Closure $closure, int $errorCode): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode($errorCode);
        $closure();
    }
}