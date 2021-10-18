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
    Element,
    Exception
};


/** @covers \MensBeam\HTML\DOM\ChildNode */
class TestChildNode extends \PHPUnit\Framework\TestCase {
    /**
     * @covers \MensBeam\HTML\DOM\ChildNode::after
     * @covers \MensBeam\HTML\DOM\ChildNode::before
     * @covers \MensBeam\HTML\DOM\ChildNode::replaceWith
     * @covers \MensBeam\HTML\DOM\NodeTrait::convertNodesToNode
     */
    public function testAfterBeforeReplaceWith(): void {
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
        $this->assertSame('<body><div></div>ook<span></span>eek<div></div></body>', (string)$d->body);

        // On node with no parent
        $c = $d->createComment('ook');
        $this->assertNull($c->after($d->createTextNode('ook')));

        // On node with parent
        $br = $d->body->insertBefore($d->createElement('br'), $div);
        $e = $d->createTextNode('eek');
        $div->before($d->createElement('span'), $o, 'eek', $e, $br);
        $this->assertSame('<body><span></span>ookeekeek<br><div></div><span></span>eek<div></div></body>', (string)$d->body);
        $div->before($o);
        $this->assertSame('<body><span></span>eekeek<br>ook<div></div><span></span>eek<div></div></body>', (string)$d->body);

        // On node with no parent
        $c = $d->createComment('ook');
        $this->assertNull($c->before($d->createTextNode('ook')));

        // On node with parent
        $s = $d->createElement('span');
        $br->replaceWith('ack', $o, $e, $s);
        $this->assertSame('<body><span></span>eekackookeek<span></span><div></div><span></span>eek<div></div></body>', (string)$d->body);
        $s->replaceWith($o);
        $this->assertSame('<body><span></span>eekackeekook<div></div><span></span>eek<div></div></body>', (string)$d->body);

        // On node with no parent
        $c = $d->createComment('ook');
        $this->assertNull($c->replaceWith($d->createTextNode('ook')));

        // Parent within node
        $o->replaceWith('poo', $o, $e);
        $this->assertSame('<body><span></span>eekackpooookeek<div></div><span></span>eek<div></div></body>', (string)$d->body);
    }


    public function provideAfterBeforeReplaceWithFailures(): array {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $div = $d->body->appendChild($d->createElement('div'));

        return [
            [ function() use($div) {
                $div->after(false);
            } ],
            [ function() use($div) {
                $div->before(false);
            } ],
            [ function() use($div) {
                $div->replaceWith(false);
            } ],
            [ function() use($div) {
                $div->after(new \DateTime);
            } ],
            [ function() use($div) {
                $div->before(new \DateTime);
            } ],
            [ function() use($div) {
                $div->replaceWith(new \DateTime);
            } ]
        ];
    }

    /**
     * @dataProvider provideAfterBeforeReplaceWithFailures
     * @covers \MensBeam\HTML\DOM\ChildNode::after
     * @covers \MensBeam\HTML\DOM\ChildNode::before
     * @covers \MensBeam\HTML\DOM\ChildNode::replaceWith
     */
    public function testAfterBeforeReplaceWithFailures(\Closure $closure): void {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::ARGUMENT_TYPE_ERROR);
        $closure();
    }


    /** @covers \MensBeam\HTML\DOM\ChildNode::moonwalk */
    public function testMoonwalk(): void {
        // Test removal of elements when moonwalking
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $div = $d->body->appendChild($d->createElement('div'));
        $div = $div->appendChild($d->createElement('div'));
        $div = $div->appendChild($d->createElement('div'));
        $div->setAttribute('class', 'delete-me');
        $div = $div->appendChild($d->createElement('div'));
        $t = $div->appendChild($d->createTextNode('ook'));

        $divs = $t->moonwalk(function($n) {
            return ($n instanceof Element && $n->nodeName === 'div');
        });

        foreach ($divs as $div) {
            if ($div->getAttribute('class') === 'delete-me') {
                $div->parentNode->removeChild($div);
            }
        }

        $this->assertSame('<body><div><div></div></div></body>', (string)$d->body);

        // Test moonwalking through template barriers
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $t = $d->body->appendChild($d->createElement('template'));

        $w = $t->content->appendChild($d->createTextNode('ook'))->moonwalk();
        $this->assertTrue($t->content->isSameNode($w->current()));
        $w->next();
        $this->assertTrue($t->isSameNode($w->current()));
    }

    public function provideMoonwalkFailures(): iterable {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $d->body->innerHTML = '<header><h1>Ook</h1></header><main><h2>Eek</h2><p>Ook <a href="ook">eek</a>, ook?</p></main><footer></footer>';
        $main = $d->body->getElementsByTagName('main')->item(0);

        return [
            [ function() use ($main) {
                $main->moonwalk(function($n) {
                    return 'ook';
                })->current();
            } ],
            [ function() use ($main) {
                $main->moonwalk(function($n) {
                    return new \DateTime();
                })->current();
            } ]
        ];
    }

    /**
     * @dataProvider provideMoonwalkFailures
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\ChildNode::moonwalk
     */
    public function testMoonwalkFailures(\Closure $closure): void {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::CLOSURE_RETURN_TYPE_ERROR);
        $closure();
    }


    public function provideWalkFailures(): iterable {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $d->body->innerHTML = '<header><h1>Ook</h1></header><main><h2>Eek</h2><p>Ook <a href="ook">eek</a>, ook?</p></main><footer></footer>';

        return [
            [ function() use ($d) {
                $d->body->firstChild->walkFollowing(function($n) {
                    return 'ook';
                })->current();
            } ],
            [ function() use ($d) {
                $d->body->firstChild->walkFollowing(function($n) {
                    return new \DateTime();
                })->current();
            } ],
            [ function() use ($d) {
                $d->body->lastChild->walkPreceding(function($n) {
                    return 'ook';
                })->current();
            } ],
            [ function() use ($d) {
                $d->body->lastChild->walkPreceding(function($n) {
                    return new \DateTime();
                })->current();
            } ]
        ];
    }

    /**
     * @dataProvider provideWalkFailures
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\ChildNode::walkFollowing
     * @covers \MensBeam\HTML\DOM\ChildNode::walkPreceding
     */
    public function testWalkFailures(\Closure $closure): void {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::CLOSURE_RETURN_TYPE_ERROR);
        $closure();
    }


    /** @covers \MensBeam\HTML\DOM\ParentNode::walk */
    public function testWalkPreceding(): void {
        // Test removal of elements when walking
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $d->body->innerHTML = '<header><h1>Ook</h1></header><main><h2>Eek</h2><p>Ook <a href="ook">eek</a>, ook?</p></main><footer></footer>';

        $this->assertNotNull($d->body->lastChild->walkPreceding(function($n) {
            return ($n instanceof Element && $n->nodeName === 'main');
        }, true)->current());
    }
}