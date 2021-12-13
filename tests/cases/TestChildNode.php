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
}