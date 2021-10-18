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
    DOMException
};
use MensBeam\HTML\Parser;


/** @covers \MensBeam\HTML\DOM\Element */
class TestElement extends \PHPUnit\Framework\TestCase {
    /** @covers \MensBeam\HTML\DOM\Element::getAttributeNames */
    public function testGetAttributeNames(): void {
        $d = new Document();
        $e = $d->createElement('html');
        $d->appendChild($e);

        $this->assertSame([], $e->getAttributeNames());

        $e->setAttribute('ook:eek', 'ook');
        $e->setAttributeNS(Parser::XMLNS_NAMESPACE, 'xmlns:xlink', Parser::XLINK_NAMESPACE);
        $e->setAttribute('ook', 'eek');

        $this->assertSame([
            'ook:eek',
            'xmlns:xlink',
            'ook'
        ], $e->getAttributeNames());
    }


    public function provideGetHasSetAttribute(): iterable {
        return [
            [ 'ook', 'eek', 'ook', 'eek' ],
            [ 'ook:eek', 'ook', 'ook:eek', 'ook' ],
            [ 'poop💩', 'soccer', 'poop💩', 'soccer' ]
        ];
    }

    /**
     * @dataProvider provideGetHasSetAttribute
     * @covers \MensBeam\HTML\DOM\Element::getAttribute
     * @covers \MensBeam\HTML\DOM\Element::hasAttribute
     * @covers \MensBeam\HTML\DOM\Element::setAttribute
     */
    public function testGetHasSetAttribute(string $nameIn, string $valueIn, string $nameExpected, string $valueExpected): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $e = $d->documentElement;
        $e->setAttribute($nameIn, $valueIn);
        $this->assertTrue($e->hasAttribute($nameExpected));
        $this->assertSame($valueExpected, $e->getAttribute($nameExpected));
    }


    public function provideGetHasSetAttributeNS(): iterable {
        return [
            [ 'fake_ns', 'ook', 'eek', 'ookeek', 'ook', 'eek', 'ookeek' ],
            [ 'another_fake_ns', 'steaming💩', 'poop💩', 'soccer', 'steaming💩', 'poop💩', 'soccer' ],
            [ Parser::XMLNS_NAMESPACE, 'xmlns', 'xlink', Parser::XLINK_NAMESPACE, 'xmlns', 'xlink', Parser::XLINK_NAMESPACE ]
        ];
    }

    /**
     * @dataProvider provideGetHasSetAttributeNS
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNS
     * @covers \MensBeam\HTML\DOM\Element::hasAttributeNS
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNS
     */
    public function testGetHasSetAttributeNS(?string $namespaceIn, ?string $prefixIn, string $localNameIn, string $valueIn, ?string $prefixExpected, string $localNameExpected, string $valueExpected): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $e = $d->documentElement;
        $qualifiedNameIn = ($prefixIn === null || $prefixIn === '') ? $localNameIn : "{$prefixIn}:{$localNameIn}";
        $e->setAttributeNS($namespaceIn, $qualifiedNameIn, $valueIn);
        $this->assertTrue($e->hasAttributeNS($namespaceIn, $localNameExpected));
        $this->assertSame($valueExpected, $e->getAttributeNS($namespaceIn, $localNameExpected));
    }


    /**
     * @covers \MensBeam\HTML\DOM\Element::__get_classList
     * @covers \MensBeam\HTML\DOM\TokenList::__construct
     */
    public function testPropertyGetClassList(): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->setAttribute('class', 'ook eek ack  ookeek');
        $this->assertSame(4, $d->documentElement->classList->length);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Element::__get_innerHTML
     * @covers \MensBeam\HTML\DOM\Element::__set_innerHTML
     * @covers \MensBeam\HTML\DOM\Document::importNode
     */
    public function testPropertyGetSetInnerHTML(): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $s = $d->body->appendChild($d->createElement('span'));
        $s->appendChild($d->createTextNode('ook'));
        $this->assertSame('<span>ook</span>', $d->body->innerHTML);

        $d->body->innerHTML = '<div id ="ook">eek</div>';
        $this->assertSame('<div id="ook">eek</div>', $d->body->innerHTML);

        $t = $d->body->appendChild($d->createElement('template'));
        $t->innerHTML = 'ook';
        $this->assertSame('ook', $t->innerHTML);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Element::__get_outerHTML
     * @covers \MensBeam\HTML\DOM\Element::__set_outerHTML
     */
    public function testPropertyGetSetOuterHTML(): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $d->body->setAttribute('class', 'ook');
        $s = $d->body->appendChild($d->createElement('span'));
        $s->appendChild($d->createTextNode('ook'));
        $this->assertSame('<body class="ook"><span>ook</span></body>', $d->body->outerHTML);

        $d->body->outerHTML = '<body>eek</body>';
        $this->assertSame('<body>eek</body>', $d->body->outerHTML);

        $f = $d->createDocumentFragment();
        $div = $f->appendChild($d->createElement('div'));
        $div->outerHTML = 'ook';
        $this->assertSame('ook', (string)$f);

        $div = $d->createElement('div');
        $div->appendChild($d->createTextNode('ook'));
        $div->outerHTML = '<div>eek</div>';
        $this->assertSame('<div>ook</div>', (string)$div);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Element::__set_outerHTML
     */
    public function testPropertySetOuterHTMLFailure(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::NO_MODIFICATION_ALLOWED);
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->outerHTML = '<html>FAIL</html>';
    }


    /**
     * @covers \MensBeam\HTML\DOM\Element::getAttribute
     */
    public function testGetAttribute(): void {
        // Just need to test nonexistent attributes
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $this->assertNull($d->documentElement->getAttribute('ook'));
    }


    /**
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNodeNS
     */
    public function testGetAttributeNodeNS(): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->setAttribute('ook', 'eek');
        // Empty string namespace
        $ook = $d->documentElement->getAttributeNodeNS('', 'ook');
        $this->assertSame('eek', $ook->value);
        // Bogus attribute
        $this->assertNull($d->documentElement->getAttributeNodeNS(null, 'what'));
    }


    /**
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNS
     */
    public function testGetAttributeNS(): void {
        // Just need to test nonexistent attributes
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $this->assertNull($d->documentElement->getAttributeNS(Parser::HTML_NAMESPACE, 'ook'));
    }


    /**
     * @covers \MensBeam\HTML\DOM\Element::hasAttributeNS
     */
    public function testHasAttributeNS(): void {
        // Just need to test empty string namespace
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->setAttribute('ook', 'eek');
        $this->assertTrue($d->documentElement->hasAttributeNS('', 'ook'));
    }


    /**
     * @covers \MensBeam\HTML\DOM\Element::removeAttribute
     */
    public function testRemoveAttribute(): void {
        // Just need to test classList updates
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->classList->add('ook', 'eek');
        $d->documentElement->removeAttribute('class');
        $this->assertNull($d->documentElement->getAttribute('class'));
    }


    /**
     * @covers \MensBeam\HTML\DOM\Element::removeAttributeNS
     */
    public function testRemoveAttributeNS(): void {
        // Just need to test classList updates
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->classList->add('ook', 'eek');
        $d->documentElement->removeAttributeNS(null, 'class');
        $this->assertNull($d->documentElement->getAttribute('class'));

        $d->documentElement->setAttributeNS(Parser::XMLNS_NAMESPACE, 'xmlns', Parser::HTML_NAMESPACE);
        $d->documentElement->removeAttributeNS(Parser::XMLNS_NAMESPACE, 'xmlns');
        $this->assertNull($d->documentElement->getAttributeNS(Parser::XMLNS_NAMESPACE, 'xmlns'));
    }


    /**
     * @covers \MensBeam\HTML\DOM\Element::setAttribute
     * @covers \MensBeam\HTML\DOM\TokenList::add
     */
    public function testSetAttribute(): void {
        // Need to test classList updates
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->classList->add('ook', 'eek');
        $d->documentElement->setAttribute('class', 'ack');
        $this->assertSame('ack', $d->documentElement->classList[0]);
        // Test setting class to empty string
        $d->documentElement->setAttribute('class', '');
        $this->assertSame('', $d->documentElement->getAttribute('class'));
        // Test setting id attribute
        $d->documentElement->setAttribute('id', 'ook');
        $this->assertSame('ook', $d->documentElement->getAttribute('id'));
    }


    /**
     * @covers \MensBeam\HTML\DOM\Element::setAttribute
     * @covers \MensBeam\HTML\DOM\TokenList::add
     */
    public function testSetAttributeFailure(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::INVALID_CHARACTER);
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->setAttribute('ook eek', 'fail');
    }


    /**
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNS
     * @covers \MensBeam\HTML\DOM\TokenList::add
     */
    public function testSetAttributeNS(): void {
        $d = new Document();
        // Don't append html element and set attribute
        $de = $d->createElement('html');
        $de->setAttributeNS(null, 'id', 'ook');
        $this->assertSame('ook', $de->getAttribute('id'));
        $de->setAttributeNS(null, 'class', 'ook');
        $this->assertSame('ook', $de->getAttribute('class'));

        $de->setAttributeNS(Parser::XMLNS_NAMESPACE, 'xmlns', Parser::HTML_NAMESPACE);
        $this->assertSame(Parser::HTML_NAMESPACE, $de->getAttributeNS(Parser::XMLNS_NAMESPACE, 'xmlns'));

        $b = $d->createElement('body');
        $b->setAttributeNS(Parser::XMLNS_NAMESPACE, 'xmlns', Parser::HTML_NAMESPACE);
        $this->assertSame(Parser::HTML_NAMESPACE, $b->getAttributeNS(Parser::XMLNS_NAMESPACE, 'xmlns'));

        $t = $d->createElement('template');
        $t->setAttributeNS(Parser::XMLNS_NAMESPACE, 'xmlns', Parser::HTML_NAMESPACE);
        $this->assertSame(Parser::HTML_NAMESPACE, $t->getAttributeNS(Parser::XMLNS_NAMESPACE, 'xmlns'));

        // Test name coercion when namespace is null
        $de->setAttributeNS(null, 'poop💩', 'ook');
        $this->assertSame('ook', $de->getAttribute('poop💩'));
    }
}