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
    Element,
    Exception
};


/** @covers \MensBeam\HTML\DOM\Walk */
class TestWalk extends \PHPUnit\Framework\TestCase {
    /** @covers \MensBeam\HTML\DOM\Walk::walk */
    public function testWalk(): void {
        // Test removal of elements when walking
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $d->body->innerHTML = '<span class="one">O</span><span class="two">O</span><span class="three">O</span><span class="four">K</span>';
        $spans = $d->body->walk(function($n) {
            return ($n instanceof Element && $n->nodeName === 'span');
        });

        foreach ($spans as $s) {
            if ($s->getAttribute('class') === 'three') {
                $s->parentNode->removeChild($s);
            }
        }

        $this->assertSame('<body><span class="one">O</span><span class="two">O</span><span class="four">K</span></body>', (string)$d->body);

        // Test walking through templates' content
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $t = $d->body->appendChild($d->createElement('template'));
        $t->content->appendChild($d->createElement('ook'));

        $this->assertSame('ook', $d->body->walk(function($n) {
            return ($n instanceof Element && $n->nodeName === 'ook');
        })->current()->nodeName);
    }


    public function provideWalkFailures(): iterable {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $d->body->innerHTML = '<header><h1>Ook</h1></header><main><h2>Eek</h2><p>Ook <a href="ook">eek</a>, ook?</p></main><footer></footer>';

        return [
            [ function() use ($d) {
                $d->body->walk(function($n) {
                    return 'ook';
                })->current();
            } ],
            [ function() use ($d) {
                $d->body->walk(function($n) {
                    return new \DateTime();
                })->current();
            } ],
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
     * @covers \MensBeam\HTML\DOM\Walk::walk
     * @covers \MensBeam\HTML\DOM\Walk::walkFollowing
     * @covers \MensBeam\HTML\DOM\Walk::walkPreceding
     */
    public function testWalkFailures(\Closure $closure): void {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::CLOSURE_RETURN_TYPE_ERROR);
        $closure();
    }


    /** @covers \MensBeam\HTML\DOM\Walk::walk */
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