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
    HTMLElement,
    Node,
    XPathException,
    XPathResult
};


/** @covers \MensBeam\HTML\DOM\XPathResult */
class TestXPathResult extends \PHPUnit\Framework\TestCase {
    function testIteration(): void {
        $d = new Document('<!DOCTYPE html><html><body><span><span>Ook</span></span><span></span></body></html>');
        $result = $d->evaluate('.//span', $d);
        foreach ($result as $k => $r) {
            $this->assertSame(HTMLElement::class, $r::class);
            $this->assertSame(HTMLElement::class, $result[$k]::class);
        }
    }


    function testMethod_iterateNext(): void {
        $d = new Document('<!DOCTYPE html><html><body><span></span></body></html>');
        $result = $d->evaluate('.//span', $d);
        $this->assertSame($d->getElementsByTagName('span')[0], $result->iterateNext());
        $this->assertNull($result->iterateNext());
    }


    function testMethod_iterateNext__errors(): void {
        $this->expectException(XPathException::class);
        $this->expectExceptionCode(XPathException::TYPE_ERROR);
        $d = new Document();
        $d->evaluate('.//fail', $d, null, XPathResult::ORDERED_NODE_SNAPSHOT_TYPE)->iterateNext();
    }


    function testMethod_snapshotItem(): void {
        $d = new Document('<!DOCTYPE html><html><body><span></span></body></html>');
        $this->assertSame($d->getElementsByTagName('span')[0], $d->evaluate('.//span', $d, null, XPathResult::ORDERED_NODE_SNAPSHOT_TYPE)->snapshotItem(0));
        $this->assertNull($d->evaluate('.//span', $d, null, XPathResult::ORDERED_NODE_SNAPSHOT_TYPE)->snapshotItem(42));
    }


    function testMethod_snapshotItem__errors(): void {
        $this->expectException(XPathException::class);
        $this->expectExceptionCode(XPathException::TYPE_ERROR);
        $d = new Document();
        $d->evaluate('.//fail', $d)->snapshotItem(0);
    }

    function testMethod_validateStorage__errors(): void {
        $this->expectException(XPathException::class);
        $this->expectExceptionCode(XPathException::TYPE_ERROR);
        $d = new Document();
        $result = $d->evaluate('count(.//fail)', $d, null, XPathResult::NUMBER_TYPE);
        $result[0];
    }


    function provideProperty__errors(): iterable {
        return [
            [ function() {
                $d = new Document();
                $result = $d->evaluate('name(//fail)', $d, null, XPathResult::NUMBER_TYPE);
                $result->booleanValue;
            } ],

            [ function() {
                $d = new Document();
                $result = $d->evaluate('//fail', $d, null, XPathResult::BOOLEAN_TYPE);
                $result->numberValue;
            } ],

            [ function() {
                $d = new Document();
                $result = $d->evaluate('//fail', $d, null, XPathResult::BOOLEAN_TYPE);
                $result->singleNodeValue;
            } ],

            [ function() {
                $d = new Document();
                $result = $d->evaluate('//fail', $d, null, XPathResult::BOOLEAN_TYPE);
                $result->stringValue;
            } ]
        ];
    }

    /** @dataProvider provideProperty__errors */
    function testProperty__errors(\Closure $closure): void {
        $this->expectException(XPathException::class);
        $this->expectExceptionCode(XPathException::TYPE_ERROR);
        $closure();
    }

    function testProperty_invalidIteratorState(): void {
        $d = new Document();
        $this->assertFalse($d->evaluate('//span', $d)->invalidIteratorState);
    }


    function testProperty_offsetSet_offsetUnset(): void {
        $d = new Document('<!DOCTYPE html><html><body><span><span>Ook</span></span><span></span></body></html>');
        $result = $d->evaluate('.//span', $d);
        $result[1] = 'ook';
        $this->assertNotSame('ook', $result[1]);
        unset($result[1]);
        $this->assertSame(HTMLElement::class, $result[1]::class);
    }


    function testProperty_resultType(): void {
        $d = new Document('<!DOCTYPE html><html><body><span><span>Ook</span></span><span></span></body></html>');
        $this->assertEquals(XPathResult::ORDERED_NODE_ITERATOR_TYPE, $d->evaluate('.//span', $d->body, null, XPathResult::ORDERED_NODE_ITERATOR_TYPE)->resultType);
        $this->assertEquals(XPathResult::ORDERED_NODE_ITERATOR_TYPE, $d->evaluate('.//span', $d->body)->resultType);
        $this->assertEquals(XPathResult::NUMBER_TYPE, $d->evaluate('count(.//span)', $d->body, null, XPathResult::NUMBER_TYPE)->resultType);
        $this->assertEquals(XPathResult::NUMBER_TYPE, $d->evaluate('count(.//span)', $d->body)->resultType);
        $this->assertEquals(XPathResult::STRING_TYPE, $d->evaluate('name(.//span)', $d->body, null, XPathResult::STRING_TYPE)->resultType);
        $this->assertEquals(XPathResult::STRING_TYPE, $d->evaluate('name(.//span)', $d->body)->resultType);
        $this->assertEquals(XPathResult::BOOLEAN_TYPE, $d->evaluate('.//span', $d->body, null, XPathResult::BOOLEAN_TYPE)->resultType);
        $this->assertEquals(XPathResult::BOOLEAN_TYPE, $d->evaluate('not(.//span)', $d->body, null, XPathResult::BOOLEAN_TYPE)->resultType);
        $this->assertEquals(XPathResult::BOOLEAN_TYPE, $d->evaluate('not(.//span)', $d->body)->resultType);
        $this->assertEquals(XPathResult::UNORDERED_NODE_SNAPSHOT_TYPE, $d->evaluate('.//span', $d->body, null, XPathResult::UNORDERED_NODE_SNAPSHOT_TYPE)->resultType);
        $this->assertEquals(XPathResult::FIRST_ORDERED_NODE_TYPE, $d->evaluate('.//span', $d->body, null, XPathResult::FIRST_ORDERED_NODE_TYPE)->resultType);
    }

    function testProperty_snapshotLength(): void {
        $d = new Document('<!DOCTYPE html><html><body><span><span>Ook</span></span><span></span></body></html>');
        $this->assertEquals(3, $d->evaluate('.//span', $d, null, XPathResult::ORDERED_NODE_SNAPSHOT_TYPE)->snapshotLength);
    }


    function testProperty_snapshotLength__errors(): void {
        $this->expectException(XPathException::class);
        $this->expectExceptionCode(XPathException::TYPE_ERROR);
        $d = new Document();
        $d->evaluate('.//fail', $d)->snapshotLength;
    }
}