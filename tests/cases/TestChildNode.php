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
    Exception
};


/** @covers \MensBeam\HTML\DOM\ChildNode */
class TestChildNode extends \PHPUnit\Framework\TestCase {
    /**
     * @covers \MensBeam\HTML\DOM\ChildNode::after
     * @covers \MensBeam\HTML\DOM\Node::convertNodesToNode
     */
    public function testAfter(): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $div = $d->body->appendChild($d->createElement('div'));
        $o = $d->body->appendChild($d->createTextNode('ook'));
        $div2 = $d->body->appendChild($d->createElement('div'));

        // On node with parent
        $div->after($d->createElement('span'), $o, 'eek');
        $this->assertSame('<body><div></div><span></span>ookeek<div></div></body>', (string)$d->body);
        $div->after($o);

        // On node with no parent
        $c = $d->createComment('ook');
        $this->assertNull($c->after($d->createTextNode('ook')));
    }


    public function provideAfterFailure(): array {
        return [
            [ false ],
            [ new \DateTime() ],
        ];
    }

    /**
     * @dataProvider provideAfterFailure
     * @covers \MensBeam\HTML\DOM\ChildNode::after
     */
    public function testAfterFailure($object): void {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::ARGUMENT_TYPE_ERROR);
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $div = $d->body->appendChild($d->createElement('div'));
        $o = $d->body->appendChild($d->createTextNode('ook'));
        $div2 = $d->body->appendChild($d->createElement('div'));
        $div->after($object);
    }
}