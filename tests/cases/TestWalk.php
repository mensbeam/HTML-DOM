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
    Element
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


    /** @covers \MensBeam\HTML\DOM\Walk::walkShallow */
    public function testWalkShallowBackwards(): void {
        // Test walking backwards
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $d->body->innerHTML = '<span class="one">O</span><span class="two">O</span><span class="three">O</span><span class="four">K</span>';
        $iterator = $d->body->walkShallow(null, true);
        $count = 0;
        foreach ($iterator as $i) {
            $count++;
            if ($i->getAttribute('class') === 'two') {
                break;
            }
        }

        $this->assertSame(3, $count);
    }
}