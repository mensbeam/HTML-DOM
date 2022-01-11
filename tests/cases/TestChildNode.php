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
    Node
};


/** @covers \MensBeam\HTML\DOM\ChildNode */
class TestChildNode extends \PHPUnit\Framework\TestCase {
    /**
     * @covers \MensBeam\HTML\DOM\ChildNode::after
     * @covers \MensBeam\HTML\DOM\ChildNode::before
     * @covers \MensBeam\HTML\DOM\ChildNode::replaceWith
     *
     * @covers \MensBeam\HTML\DOM\Comment::__construct
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::createComment
     * @covers \MensBeam\HTML\DOM\Document::createDocumentFragment
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\Document::serialize
     * @covers \MensBeam\HTML\DOM\DocumentFragment::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_firstChild
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::__get_parentNode
     * @covers \MensBeam\HTML\DOM\Node::__toString
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::containsInner
     * @covers \MensBeam\HTML\DOM\Node::convertNodesToNode
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::insertBefore
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\Node::replaceChild
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
    public function testMethod_after_before_replaceWith(): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $body = $d->documentElement->appendChild($d->createElement('body'));
        $div = $body->appendChild($d->createElement('div'));
        $o = $body->appendChild($d->createTextNode('ook'));
        $div2 = $body->appendChild($d->createElement('div'));

        // On node with parent
        $div->after($d->createElement('span'), $o, 'eek');
        $this->assertSame('<body><div></div><span></span>ookeek<div></div></body>', (string)$body);
        $div->after($o);
        $this->assertSame('<body><div></div>ook<span></span>eek<div></div></body>', (string)$body);


        // On node with no parent
        $c = $d->createComment('ook');
        $this->assertNull($c->after($d->createTextNode('ook')));

        // On node with parent
        $br = $body->insertBefore($d->createElement('br'), $div);
        $e = $d->createTextNode('eek');
        $div->before($d->createElement('span'), $o, 'eek', $e, $br);
        $this->assertSame('<body><span></span>ookeekeek<br><div></div><span></span>eek<div></div></body>', (string)$body);
        $div->before($o);
        $this->assertSame('<body><span></span>eekeek<br>ook<div></div><span></span>eek<div></div></body>', (string)$body);

        // On node with no parent
        $c = $d->createComment('ook');
        $this->assertNull($c->before($d->createTextNode('ook')));

        // On node with parent
        $s = $d->createElement('span');
        $br->replaceWith('ack', $o, $e, $s);
        $this->assertSame('<body><span></span>eekackookeek<span></span><div></div><span></span>eek<div></div></body>', (string)$body);
        $s->replaceWith($o);
        $this->assertSame('<body><span></span>eekackeekook<div></div><span></span>eek<div></div></body>', (string)$body);

        // On node with no parent
        $c = $d->createComment('ook');
        $this->assertNull($c->replaceWith($d->createTextNode('ook')));

        // Parent within node
        $o->replaceWith('poo', $o, $e);
        $this->assertSame('<body><span></span>eekackpooookeek<div></div><span></span>eek<div></div></body>', (string)$body);
    }


    /**
     * @covers \MensBeam\HTML\DOM\ChildNode::remove
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::__get_parentNode
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Node::removeChild
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     */
    public function testMethod_remove(): void {
        $d = new Document('<!DOCTYPE html><html><body></body></html>');
        $body = $d->body;
        $body->remove();
        $this->assertNull($d->body);

        // Test removal of an element without a parent.
        $body->remove();
    }
}