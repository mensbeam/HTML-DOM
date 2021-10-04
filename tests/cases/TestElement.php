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
}