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
use MensBeam\HTML\Parser;


/** @covers \MensBeam\HTML\DOM\Element */
class TestElement extends \PHPUnit\Framework\TestCase {
    /*public function provideAttributeNodeSettings(): iterable {
        return [
            [true,  null,                                 null,                            "test"],
            [true,  null,                                 null,                            "TEST"],
            [true,  "http://www.w3.org/1999/xhtml",       null,                            "test"],
            [true,  "http://www.w3.org/1999/xhtml",       null,                            "TEST"],
            [true,  null,                                 null,                            "testU00003Atest"],
            [true,  null,                                 null,                            "TESTU00003ATEST"],
            [true,  "http://www.w3.org/1999/xhtml",       null,                            "testU00003Atest"],
            [true,  "http://www.w3.org/1999/xhtml",       null,                            "TESTU00003ATEST"],
            [true,  null,                                 "http://www.w3.org/1999/xhtml",  "test:test"],
            [true,  null,                                 "http://www.w3.org/1999/xhtml",  "TEST:TEST"],
            [true,  "http://www.w3.org/1998/Math/MathML", null,                            "test"],
            [true,  "http://www.w3.org/1998/Math/MathML", null,                            "TEST"],
            [true,  null,                                 "http://www.w3.org/2000/xmlns/", "xmlns:xlink"],
            [true,  null,                                 "http://www.w3.org/2000/xmlns/", "xmlns:XLINK"],
            [true,  null,                                 "fake_ns",                       "test:testU00003Atest"],
            [true,  null,                                 "fake_ns",                       "TEST:TESTU00003ATEST"],
            [false, null,                                 null,                            "test"],
            [false, null,                                 null,                            "TEST"],
            [false, "http://www.w3.org/1999/xhtml",       null,                            "test"],
            [false, "http://www.w3.org/1999/xhtml",       null,                            "TEST"],
            [false, null,                                 null,                            "testU00003Atest"],
            [false, null,                                 null,                            "TESTU00003ATEST"],
            [false, "http://www.w3.org/1999/xhtml",       null,                            "testU00003Atest"],
            [false, "http://www.w3.org/1999/xhtml",       null,                            "TESTU00003ATEST"],
            [false, null,                                 "http://www.w3.org/1999/xhtml",  "test:test"],
            [false, null,                                 "http://www.w3.org/1999/xhtml",  "TEST:TEST"],
            [false, "http://www.w3.org/1998/Math/MathML", null,                            "test"],
            [false, "http://www.w3.org/1998/Math/MathML", null,                            "TEST"],
            [false, null,                                 "http://www.w3.org/2000/xmlns/", "xmlns:xlink"],
            [false, null,                                 "http://www.w3.org/2000/xmlns/", "xmlns:XLINK"],
            [false, null,                                 "fake_ns",                       "test:testU00003Atest"],
            [false, null,                                 "fake_ns",                       "TEST:TESTU00003ATEST"],
        ];
    }*/

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


    /*public function provideGetHasSetAttributeNS(): iterable {
        return [
            [ 'http://www.w3.org/1999/xhtml', null, 'ook', 'eek', null, 'ook', 'eek' ],
            [ 'fake_ns', 'ook', 'eek', 'ookeek', 'ook', 'eek', 'ookeek' ],
            [ 'another_fake_ns', 'steaming💩', 'poop💩', 'soccer', 'steaming💩', 'poop💩', 'soccer' ]
        ];
    }

    **
     * @dataProvider provideGetHasSetAttributeNS
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNS
     * @covers \MensBeam\HTML\DOM\Element::hasAttributeNS
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNS
     *
    public function testGetHasSetAttributeNS(string $namespaceIn, string $prefixIn, string $localNameIn, string $valueIn, string $prefixExpected, string $localNameExpected, string $valueExpected): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $e = $d->documentElement;
        $e->setAttributeNS($namespaceURI, "{$prefixIn}:{$localNameIn}", $valueIn);
        $this->assertTrue($e->hasAttribute($localNameExpected));
        $this->assertSame($valueExpected, $e->getAttribute($localNameExpected));
    }*/
}