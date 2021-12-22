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


/** @covers \MensBeam\HTML\DOM\HTMLElement */
class TestHTMLElement extends \PHPUnit\Framework\TestCase {
    /**
     * @covers \MensBeam\HTML\DOM\HTMLElement::__get_innerText
     * @covers \MensBeam\HTML\DOM\HTMLElement::__set_innerText
     * @covers \MensBeam\HTML\DOM\HTMLElement::__get_outerText
     * @covers \MensBeam\HTML\DOM\HTMLElement::__set_outerText
     *
     * @covers \MensBeam\HTML\DOM\Collection::__construct
     * @covers \MensBeam\HTML\DOM\Collection::__get_length
     * @covers \MensBeam\HTML\DOM\Collection::count
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_innerHTML
     * @covers \MensBeam\HTML\DOM\Element::getRenderedTextFragment
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_childNodes
     * @covers \MensBeam\HTML\DOM\Node::__get_parentNode
     * @covers \MensBeam\HTML\DOM\Node::__get_textContent
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\Text::__construct
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
    public function testProperty_innerText_outerText() {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $body = $d->body;
        $body->appendChild($d->createTextNode('ook '));
        $s = $body->appendChild($d->createElement('span'));
        $s->appendChild($d->createTextNode('ook'));
        $body->appendChild($d->createTextNode(' eek'));
        $this->assertSame('ook <span>ook</span> eek', $body->innerHTML);

        $s->innerText = <<<TEXT
        ook\r\n
            eek ook
        TEXT;
        $this->assertSame('ook ookook    eek ook eek', $body->innerText);
        $this->assertSame('ook<br><br>ook    eek ook', $s->innerHTML);

        $s->outerText = 'ack';
        $this->assertSame('ook ack eek', $body->outerText);
        $this->assertEquals(1, $body->childNodes->length);

        $s = $body->appendChild($d->createElement('span'));
        $s->outerText = '';
        $this->assertSame('ook ack eek', $body->outerText);
    }


    /**
     * @covers \MensBeam\HTML\DOM\HTMLElement::__set_outerText
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_parentNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testProperty_outerText__errors() {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::NO_MODIFICATION_ALLOWED);
        $d = new Document();
        $h = $d->createElement('html');
        $h->outerText = 'fail';
    }
}