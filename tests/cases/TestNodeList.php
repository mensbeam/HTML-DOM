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
    Exception,
    NodeList
};


/** @covers \MensBeam\HTML\DOM\NodeList */
class TestNodeList extends \PHPUnit\Framework\TestCase {
    public function provideConstructorFailures(): iterable {
        return [
            [ function() {
                new NodeList([ 'fail' ]);
            } ],
            [ function() {
                new NodeList([ new \DateTime() ]);
            } ]
        ];
    }

    /**
     * @dataProvider provideConstructorFailures
     * @covers \MensBeam\HTML\DOM\NodeList::__construct
     */
    public function testConstructorFailures(\Closure $closure): void {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::ARGUMENT_TYPE_ERROR);
        $closure();
    }


    /** @covers \MensBeam\HTML\DOM\NodeList::count */
    public function testCount(): void {
        $d = new Document();
        $list = new NodeList([
            $d->createElement('ook'),
            $d->createTextNode('eek'),
            $d->createComment('ack')
        ]);
        $this->assertEquals(3, count($list));
    }


    /** @covers \MensBeam\HTML\DOM\NodeList::item */
    public function testItem(): void {
        $d = new Document();
        $list = new NodeList([
            $d->createElement('ook'),
            $d->createTextNode('eek'),
            $d->createComment('ack')
        ]);
        $this->assertNull($list->item(42));
    }


    /**
     * @covers \MensBeam\HTML\DOM\NodeList::current
     * @covers \MensBeam\HTML\DOM\NodeList::item
     * @covers \MensBeam\HTML\DOM\NodeList::key
     * @covers \MensBeam\HTML\DOM\NodeList::next
     * @covers \MensBeam\HTML\DOM\NodeList::rewind
     * @covers \MensBeam\HTML\DOM\NodeList::offsetExists
     * @covers \MensBeam\HTML\DOM\NodeList::offsetGet
     * @covers \MensBeam\HTML\DOM\NodeList::valid
     */
    public function testIteration(): void {
        $d = new Document();
        $list = new NodeList([
            $d->createElement('ook'),
            $d->createTextNode('eek'),
            $d->createComment('ack')
        ]);

        foreach ($list as $key => $node) {
            $this->assertSame($node, $list[$key]);
            // test offsetExists
            $this->assertTrue(isset($list[$key]));
        }
    }


    /** @covers \MensBeam\HTML\DOM\NodeList::__get_length */
    public function testPropertyGetLength(): void {
        $d = new Document();
        $list = new NodeList([
            $d->createElement('ook'),
            $d->createTextNode('eek'),
            $d->createComment('ack')
        ]);
        $this->assertEquals(3, $list->length);
    }
}