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


/** @covers \MensBeam\HTML\DOM\Moonwalk */
class TestMoonwalk extends \PHPUnit\Framework\TestCase {
    /** @covers \MensBeam\HTML\DOM\Moonwalk::moonwalk */
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
     * @covers \MensBeam\HTML\DOM\Walk::walk
     * @covers \MensBeam\HTML\DOM\Walk::walkFollowing
     * @covers \MensBeam\HTML\DOM\Walk::walkPreceding
     */
    public function testMoonwalkFailures(\Closure $closure): void {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::CLOSURE_RETURN_TYPE_ERROR);
        $closure();
    }
}