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


/** @covers \MensBeam\HTML\DOM\DOMTokenList */
class TestDOMTokenList extends \PHPUnit\Framework\TestCase {
    public function provideMethod_add_remove_replace_toggle___errors(): iterable {
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
     * @dataProvider provideMethod_add_remove_replace_toggle___errors
     * @covers \MensBeam\HTML\DOM\DOMTokenList::add
     * @covers \MensBeam\HTML\DOM\DOMTokenList::remove
     * @covers \MensBeam\HTML\DOM\DOMTokenList::replace
     * @covers \MensBeam\HTML\DOM\DOMTokenList::toggle
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_classList
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     */
    public function testMethod_add_remove_replace_toggle___errors(\Closure $closure, int $errorCode): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode($errorCode);
        $closure();
    }


    /**
     * @covers \MensBeam\HTML\DOM\DOMTokenList::contains
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__toString
     * @covers \MensBeam\HTML\DOM\DOMTokenList::add
     * @covers \MensBeam\HTML\DOM\DOMTokenList::parseOrderedSet
     * @covers \MensBeam\HTML\DOM\DOMTokenList::update
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_classList
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
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
    public function testMethod_contains() {
        $d = new Document();
        $e = $d->appendChild($d->createElement('html'));
        $e->classList->add('ook', 'eek', 'ack', 'ookeek');
        $this->assertTrue($e->classList->contains('ack'));
        $this->assertFalse($e->classList->contains('fail'));
    }


    /**
     * @covers \MensBeam\HTML\DOM\DOMTokenList::count
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__toString
     * @covers \MensBeam\HTML\DOM\DOMTokenList::add
     * @covers \MensBeam\HTML\DOM\DOMTokenList::parseOrderedSet
     * @covers \MensBeam\HTML\DOM\DOMTokenList::update
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_classList
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
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
    public function testMethod_count(): void {
        $d = new Document();
        $e = $d->appendChild($d->createElement('html'));
        $this->assertEquals(0, count($e->classList));
        $e->classList->add('ook', 'eek', 'ack', 'ookeek');
        $this->assertEquals(4, count($e->classList));
    }


    /**
     * @covers \MensBeam\HTML\DOM\DOMTokenList::item
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__get_length
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__toString
     * @covers \MensBeam\HTML\DOM\DOMTokenList::add
     * @covers \MensBeam\HTML\DOM\DOMTokenList::parseOrderedSet
     * @covers \MensBeam\HTML\DOM\DOMTokenList::update
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_classList
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
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
    public function testMethod_item(): void {
        $d = new Document();
        $e = $d->appendChild($d->createElement('html'));
        $e->classList->add('ook', 'eek', 'ack', 'ookeek');
        $this->assertNull($e->classList->item(42));
        $this->assertEquals(4, $e->classList->length);
    }

    /**
     * @covers \MensBeam\HTML\DOM\DOMTokenList::offsetSet
     * @covers \MensBeam\HTML\DOM\DOMTokenList::offsetUnset
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__get_length
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__toString
     * @covers \MensBeam\HTML\DOM\DOMTokenList::add
     * @covers \MensBeam\HTML\DOM\DOMTokenList::parseOrderedSet
     * @covers \MensBeam\HTML\DOM\DOMTokenList::update
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_classList
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
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
    public function testMethod_offsetSet_offsetUnset(): void {
        $d = new Document();
        $e = $d->appendChild($d->createElement('html'));
        $e->classList->add('ook', 'eek', 'ack', 'ookeek');
        $e->classList[3] = 'ook';
        unset($e->classList[3]);
        $this->assertEquals(4, $e->classList->length);
    }


    /**
     * @covers \MensBeam\HTML\DOM\DOMTokenList::replace
     *
     * @covers \MensBeam\HTML\DOM\Attr::__get_value
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__get_value
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__toString
     * @covers \MensBeam\HTML\DOM\DOMTokenList::parseOrderedSet
     * @covers \MensBeam\HTML\DOM\DOMTokenList::update
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_classList
     * @covers \MensBeam\HTML\DOM\Element::__get_namespaceURI
     * @covers \MensBeam\HTML\DOM\Element::getAttribute
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNode
     * @covers \MensBeam\HTML\DOM\Element::setAttribute
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
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
    public function testMethod_replace(): void {
        $d = new Document();
        $e = $d->appendChild($d->createElement('html'));
        $e->setAttribute('class', 'ook eek ack ookeek');
        $this->assertTrue($e->classList->replace('ack', 'what'));
        $this->assertSame('ook eek what ookeek', $e->classList->value);
        $this->assertSame('ook eek what ookeek', $e->getAttribute('class'));
        $this->assertFalse($e->classList->replace('fail', 'eekook'));
    }


    /**
     * @covers \MensBeam\HTML\DOM\DOMTokenList::remove
     * @covers \MensBeam\HTML\DOM\Attr::__get_value
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__get_length
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__get_value
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__toString
     * @covers \MensBeam\HTML\DOM\DOMTokenList::item
     * @covers \MensBeam\HTML\DOM\DOMTokenList::offsetGet
     * @covers \MensBeam\HTML\DOM\DOMTokenList::parseOrderedSet
     * @covers \MensBeam\HTML\DOM\DOMTokenList::update
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_classList
     * @covers \MensBeam\HTML\DOM\Element::__get_namespaceURI
     * @covers \MensBeam\HTML\DOM\Element::getAttribute
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNode
     * @covers \MensBeam\HTML\DOM\Element::setAttribute
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
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
    public function testMethod_remove(): void {
        $d = new Document();
        $e = $d->appendChild($d->createElement('html'));
        $e->classList->remove('ook');
        $e->setAttribute('class', 'ook eek ack ookeek');
        $e->classList->remove('ack');
        $this->assertSame('ook eek ookeek', $e->classList->value);
        $this->assertSame('ook eek ookeek', $e->getAttribute('class'));

        // It is wasteful to do it like this of course, but this is only for testing.
        $classList = $e->classList;
        while ($classList->length > 0) {
            $classList->remove($classList[0]);
        }

        $this->assertEquals(0, $e->classList->length);
    }


    /**
     * @covers \MensBeam\HTML\DOM\DOMTokenList::supports
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__toString
     * @covers \MensBeam\HTML\DOM\DOMTokenList::add
     * @covers \MensBeam\HTML\DOM\DOMTokenList::parseOrderedSet
     * @covers \MensBeam\HTML\DOM\DOMTokenList::update
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_classList
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
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
    public function testMethod_supports(): void {
        // PHPUnit is supposed to support expecting of errors, but it doesn't. So let's
        // write a bunch of bullshit so we can catch and assert errors instead.
        set_error_handler(function($errno) {
            if ($errno === \E_USER_ERROR) {
                $this->assertEquals(\E_USER_ERROR, $errno);
            }
        });

        $d = new Document();
        $e = $d->appendChild($d->createElement('html'));
        $e->classList->add('ook', 'eek', 'ack', 'ookeek');
        $e->classList->supports('ack');

        restore_error_handler();
    }


    /**
     * @covers \MensBeam\HTML\DOM\DOMTokenList::toggle
     *
     * @covers \MensBeam\HTML\DOM\Attr::__get_value
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__get_value
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__toString
     * @covers \MensBeam\HTML\DOM\DOMTokenList::add
     * @covers \MensBeam\HTML\DOM\DOMTokenList::parseOrderedSet
     * @covers \MensBeam\HTML\DOM\DOMTokenList::remove
     * @covers \MensBeam\HTML\DOM\DOMTokenList::update
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_classList
     * @covers \MensBeam\HTML\DOM\Element::__get_namespaceURI
     * @covers \MensBeam\HTML\DOM\Element::getAttribute
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNode
     * @covers \MensBeam\HTML\DOM\Element::setAttribute
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
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
    public function testMethod_toggle(): void {
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


    /**
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__toString
     * @covers \MensBeam\HTML\DOM\DOMTokenList::add
     * @covers \MensBeam\HTML\DOM\DOMTokenList::current
     * @covers \MensBeam\HTML\DOM\DOMTokenList::item
     * @covers \MensBeam\HTML\DOM\DOMTokenList::key
     * @covers \MensBeam\HTML\DOM\DOMTokenList::next
     * @covers \MensBeam\HTML\DOM\DOMTokenList::offsetExists
     * @covers \MensBeam\HTML\DOM\DOMTokenList::offsetGet
     * @covers \MensBeam\HTML\DOM\DOMTokenList::parseOrderedSet
     * @covers \MensBeam\HTML\DOM\DOMTokenList::rewind
     * @covers \MensBeam\HTML\DOM\DOMTokenList::update
     * @covers \MensBeam\HTML\DOM\DOMTokenList::valid
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_classList
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
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
    public function testProcess_iteration(): void {
        $d = new Document();
        $e = $d->appendChild($d->createElement('html'));
        $e->classList->add('ook', 'eek', 'ack', 'ookeek');

        foreach ($e->classList as $key => $className) {
            $this->assertSame($className, $e->classList[$key]);
            // test offsetExists
            $this->assertTrue(isset($e->classList[$key]));
        }
    }


    /**
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__get_value
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__set_value
     *
     * @covers \MensBeam\HTML\DOM\Attr::__get_value
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__construct
     * @covers \MensBeam\HTML\DOM\DOMTokenList::__toString
     * @covers \MensBeam\HTML\DOM\DOMTokenList::add
     * @covers \MensBeam\HTML\DOM\DOMTokenList::item
     * @covers \MensBeam\HTML\DOM\DOMTokenList::offsetGet
     * @covers \MensBeam\HTML\DOM\DOMTokenList::parseOrderedSet
     * @covers \MensBeam\HTML\DOM\DOMTokenList::update
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_classList
     * @covers \MensBeam\HTML\DOM\Element::__get_namespaceURI
     * @covers \MensBeam\HTML\DOM\Element::getAttribute
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNode
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
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
    public function testProperty_value(): void {
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
}