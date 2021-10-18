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


/** @covers \MensBeam\HTML\DOM\TokenList */
class TestTokenList extends \PHPUnit\Framework\TestCase {
    public function provideAddRemoveReplaceToggleFailures(): iterable {
        return [
            [ function() {
                $d = new Document();
                $e = $d->createElement('html');
                $e->classList->add('');
            }, DOMException::SYNTAX_ERROR ],
            [ function() {
                $d = new Document();
                $e = $d->createElement('html');
                $e->classList->remove('');
            }, DOMException::SYNTAX_ERROR ],
            [ function() {
                $d = new Document();
                $e = $d->createElement('html');
                $e->classList->replace('ack', '');
            }, DOMException::SYNTAX_ERROR ],
            [ function() {
                $d = new Document();
                $e = $d->createElement('html');
                $e->classList->toggle('');
            }, DOMException::SYNTAX_ERROR ],
            [ function() {
                $d = new Document();
                $e = $d->createElement('html');
                $e->classList->add('fail fail');
            }, DOMException::INVALID_CHARACTER ],
            [ function() {
                $d = new Document();
                $e = $d->createElement('html');
                $e->classList->remove('fail fail');
            }, DOMException::INVALID_CHARACTER ],
            [ function() {
                $d = new Document();
                $e = $d->createElement('html');
                $e->classList->replace('ack', 'fail fail');
            }, DOMException::INVALID_CHARACTER ],
            [ function() {
                $d = new Document();
                $e = $d->createElement('html');
                $e->classList->toggle('fail fail');
            }, DOMException::INVALID_CHARACTER ]
        ];
    }

    /**
     * @dataProvider provideAddRemoveReplaceToggleFailures
     * @covers \MensBeam\HTML\DOM\TokenList::add
     * @covers \MensBeam\HTML\DOM\TokenList::remove
     * @covers \MensBeam\HTML\DOM\TokenList::replace
     * @covers \MensBeam\HTML\DOM\TokenList::toggle
     */
    public function testAddRemoveReplaceFailures(\Closure $closure, int $errorCode): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode($errorCode);
        $closure();
    }


    /** @covers \MensBeam\HTML\DOM\TokenList::contains */
    public function testContains(): void {
        $d = new Document();
        $e = $d->appendChild($d->createElement('html'));
        $e->classList->add('ook', 'eek', 'ack', 'ookeek');
        $this->assertTrue($e->classList->contains('ack'));
        $this->assertFalse($e->classList->contains('fail'));
    }


    /** @covers \MensBeam\HTML\DOM\TokenList::count */
    public function testCount(): void {
        $d = new Document();
        $e = $d->appendChild($d->createElement('html'));
        $e->classList->add('ook', 'eek', 'ack', 'ookeek');
        $this->assertSame(4, count($e->classList));
    }


    /** @covers \MensBeam\HTML\DOM\TokenList::item */
    public function testItem(): void {
        $d = new Document();
        $e = $d->appendChild($d->createElement('html'));
        $e->classList->add('ook', 'eek', 'ack', 'ookeek');
        $this->assertNull($e->classList->item(42));
    }


    /**
     * @covers \MensBeam\HTML\DOM\TokenList::current
     * @covers \MensBeam\HTML\DOM\TokenList::item
     * @covers \MensBeam\HTML\DOM\TokenList::key
     * @covers \MensBeam\HTML\DOM\TokenList::next
     * @covers \MensBeam\HTML\DOM\TokenList::rewind
     * @covers \MensBeam\HTML\DOM\TokenList::offsetExists
     * @covers \MensBeam\HTML\DOM\TokenList::offsetGet
     * @covers \MensBeam\HTML\DOM\TokenList::valid
     */
    public function testIteration(): void {
        $d = new Document();
        $e = $d->appendChild($d->createElement('html'));
        $e->classList->add('ook', 'eek', 'ack', 'ookeek');

        foreach ($e->classList as $key => $className) {
            $this->assertSame($className, $e->classList[$key]);
            // test offsetExists
            $this->assertTrue(isset($e->classList[$key]));
        }
    }

    /** @covers \MensBeam\HTML\DOM\TokenList::__get_length */
    public function testPropertyGetLength(): void {
        // Test it with and without an attached document element
        $d = new Document();
        $e = $d->appendChild($d->createElement('html'));
        $e->classList->add('ook', 'eek', 'ack', 'ookeek');
        $this->assertSame(4, $e->classList->length);

        $d = new Document();
        $e = $d->createElement('html');
        $e->classList->add('ook', 'eek', 'ack', 'ookeek');
        $this->assertSame(4, $e->classList->length);
    }


    /**
     * @covers \MensBeam\HTML\DOM\TokenList::__get_value
     * @covers \MensBeam\HTML\DOM\TokenList::__set_value
     */
    public function testPropertyGetSetValue(): void {
        // Test it with and without an attached document element
        $d = new Document();
        $e = $d->createElement('html');
        $e->classList->add('ook', 'eek', 'ack', 'ookeek');
        $this->assertSame('ook eek ack ookeek', $e->classList->value);
        $this->assertSame('ook eek ack ookeek', $e->getAttribute('class'));
        $e->classList->value = 'omg wtf bbq lol zor bor xxx';
        $this->assertSame('lol', $e->classList[3]);
        $this->assertSame('omg wtf bbq lol zor bor xxx', $e->classList->value);
        $this->assertSame('omg wtf bbq lol zor bor xxx', $e->getAttribute('class'));
        $e->classList->value = '';
        $this->assertSame('', $e->classList->value);
        $this->assertSame('', $e->getAttribute('class'));

        $d = new Document();
        $e = $d->appendChild($d->createElement('html'));
        $e->classList->add('ook', 'eek', 'ack', 'ookeek');
        $this->assertSame('ook eek ack ookeek', $e->classList->value);
        $this->assertSame('ook eek ack ookeek', $e->getAttribute('class'));
        $e->classList->value = 'omg wtf bbq lol zor bor xxx';
        $this->assertSame('lol', $e->classList[3]);
        $this->assertSame('omg wtf bbq lol zor bor xxx', $e->classList->value);
        $this->assertSame('omg wtf bbq lol zor bor xxx', $e->getAttribute('class'));
        $e->classList->value = '';
        $this->assertSame('', $e->classList->value);
        $this->assertSame('', $e->getAttribute('class'));
    }


    /** @covers \MensBeam\HTML\DOM\TokenList::replace */
    public function testReplace(): void {
        $d = new Document();
        $e = $d->appendChild($d->createElement('html'));
        $e->setAttribute('class', 'ook eek ack ookeek');
        $this->assertTrue($e->classList->replace('ack', 'what'));
        $this->assertSame('ook eek what ookeek', $e->classList->value);
        $this->assertSame('ook eek what ookeek', $e->getAttribute('class'));
        $this->assertFalse($e->classList->replace('fail', 'eekook'));
    }


    /** @covers \MensBeam\HTML\DOM\TokenList::remove */
    public function testRemove(): void {
        $d = new Document();
        $e = $d->appendChild($d->createElement('html'));
        $e->setAttribute('class', 'ook eek ack ookeek');
        $e->classList->remove('ack');
        $this->assertSame('ook eek ookeek', $e->classList->value);
        $this->assertSame('ook eek ookeek', $e->getAttribute('class'));
    }


    /** @covers \MensBeam\HTML\DOM\TokenList::supports */
    public function testSupports(): void {
        $d = new Document();
        $e = $d->appendChild($d->createElement('html'));
        $e->classList->add('ook', 'eek', 'ack', 'ookeek');
        $this->assertTrue($e->classList->supports('ack'));
    }


    /** @covers \MensBeam\HTML\DOM\TokenList::toggle */
    public function testToggle(): void {
        $d = new Document();
        $e = $d->appendChild($d->createElement('html'));
        $e->setAttribute('class', 'ook eek ack ookeek');
        $this->assertFalse($e->classList->toggle('ack'));
        $this->assertSame('ook eek ookeek', $e->classList->value);
        $this->assertSame('ook eek ookeek', $e->getAttribute('class'));
        $this->assertTrue($e->classList->toggle('ack'));
        $this->assertSame('ook eek ookeek ack', $e->classList->value);
        $this->assertSame('ook eek ookeek ack', $e->getAttribute('class'));
        $this->assertTrue($e->classList->toggle('ack', true));
        $this->assertSame('ook eek ookeek ack', $e->classList->value);
        $this->assertSame('ook eek ookeek ack', $e->getAttribute('class'));
        $this->assertFalse($e->classList->toggle('eekook', false));
        $this->assertSame('ook eek ookeek ack', $e->classList->value);
        $this->assertSame('ook eek ookeek ack', $e->getAttribute('class'));
        $this->assertTrue($e->classList->toggle('eekook', true));
        $this->assertSame('ook eek ookeek ack eekook', $e->classList->value);
        $this->assertSame('ook eek ookeek ack eekook', $e->getAttribute('class'));
    }
}