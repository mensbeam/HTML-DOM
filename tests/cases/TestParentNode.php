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
    DOMException,
    Element,
    ElementMap,
    Exception
};


/** @covers \MensBeam\HTML\DOM\ParentNode */
class TestParentNode extends \PHPUnit\Framework\TestCase {
    /** @covers \MensBeam\HTML\DOM\ParentNode::insertBefore */
    public function testInsertBefore(): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $div = $d->body->appendChild($d->createElement('div'));
        $ook = $d->body->insertBefore($d->createTextNode('ook'), $div);

        $this->assertSame('<body>ook<div></div></body>', (string)$d->body);

        $t = $d->body->insertBefore($d->createElement('template'), $ook);

        $this->assertSame('<body><template></template>ook<div></div></body>', (string)$d->body);
        $this->assertTrue(ElementMap::has($t));
    }


    public function providePreInsertionValidationFailures(): iterable {
        return [
            [ function() {
                $d = new Document();
                $d->appendChild($d->createElement('html'));
                $b = $d->documentElement->appendChild($d->createElement('body'));
                $b->appendChild($d->documentElement);
            } ],
            [ function() {
                $d = new Document();
                $d->appendChild($d->createElement('html'));
                $b = $d->documentElement->appendChild($d->createElement('body'));
                $t = $b->appendChild($d->createElement('template'));
                $t->content->appendChild($b);
            } ],
            [ function() {
                $d = new Document();
                $d->appendChild($d->createElement('html'));
                $b = $d->documentElement->appendChild($d->createElement('body'));
                $d->insertBefore($d->createElement('fail'), $b);
            }, DOMException::NOT_FOUND ],
            [ function() {
                $d = new Document();
                $df = $d->createDocumentFragment();
                $df->appendChild($d->createElement('html'));
                $df->appendChild($d->createTextNode(' '));
                $d->appendChild($df);
            } ],
            [ function() {
                $d = new Document();
                $d->appendChild($d);
            } ],
            [ function() {
                $d = new Document();
                $d->appendChild($d->implementation->createDocumentType('html'));
                $d->appendChild($d->implementation->createDocumentType('html'));
            } ],
            [ function() {
                $d = new Document();
                $d->appendChild($d->createElement('html'));
                $d->appendChild($d->implementation->createDocumentType('html'));
            } ],
            [ function() {
                $d = new Document();
                $d->appendChild($d->createElement('html'));
                $c = $d->appendChild($d->createComment('ook'));
                $d->insertBefore($d->implementation->createDocumentType('html'), $c);
            } ],
            [ function() {
                $d = new Document();
                $d->appendChild($d->createElement('html'));
                $d->documentElement->insertBefore($d->implementation->createDocumentType('html'));
            } ],
            [ function() {
                $d = new Document();
                $d->appendChild($d->createElement('html'));
                $d->documentElement->insertBefore($d->implementation->createDocumentType('html'));
            } ],
            [ function() {
                $d = new Document();
                $dt = $d->appendChild($d->implementation->createDocumentType('html'));
                $df = $d->createDocumentFragment();
                $df->appendChild($d->createElement('html'));
                $d->insertBefore($df, $dt);
            } ],
            [ function() {
                $d = new Document();
                $c = $d->appendChild($d->createComment('OOK'));
                $d->appendChild($d->implementation->createDocumentType('html'));
                $df = $d->createDocumentFragment();
                $df->appendChild($d->createElement('html'));
                $d->insertBefore($df, $c);
            } ],
            [ function() {
                $d = new Document();
                $dt = $d->appendChild($d->implementation->createDocumentType('html'));
                $d->insertBefore($d->createElement('html'), $dt);
            } ],
            [ function() {
                $d = new Document();
                $c = $d->appendChild($d->createComment('OOK'));
                $d->appendChild($d->implementation->createDocumentType('html'));
                $d->insertBefore($d->createElement('html'), $c);
            } ],
            [ function() {
                $d = new Document();
                $d->appendChild($d->createElement('html'));
                $d->appendChild($d->createElement('body'));
            } ]
        ];
    }

    /**
     * @dataProvider providePreInsertionValidationFailures
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\NodeTrait::getRootNode
     * @covers \MensBeam\HTML\DOM\ParentNode::preInsertionValidity
     */
    public function testPreInsertionValidationFailures(\Closure $closure, int $errorCode = DOMException::HIERARCHY_REQUEST_ERROR): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode($errorCode);
        $closure();
    }


    /** @covers \MensBeam\HTML\DOM\ParentNode::__get_children */
    public function testPropertyGetChildren(): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));

        $this->assertSame(1, $d->documentElement->children->length);
    }


    /** @covers \MensBeam\HTML\DOM\ParentNode::replaceChild */
    public function testReplaceChild(): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $div = $d->body->appendChild($d->createElement('div'));
        $ook = $d->body->replaceChild($d->createTextNode('ook'), $div);

        $this->assertSame('<body>ook</body>', (string)$d->body);

        $t = $d->body->replaceChild($d->createElement('template'), $ook);

        $this->assertSame('<body><template></template></body>', (string)$d->body);
        $this->assertTrue(ElementMap::has($t));

        $d->body->replaceChild($d->createElement('br'), $t);

        $this->assertSame('<body><br></body>', (string)$d->body);
        $this->assertFalse(ElementMap::has($t));
    }


    /** @covers \MensBeam\HTML\DOM\ParentNode::replaceChildren */
    public function testReplaceChildren(): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $div = $d->body->appendChild($d->createElement('div'));
        $ook = $d->body->appendChild($d->createTextNode('ook'), $d->body->appendChild($d->createElement('div')));

        $d->body->replaceChildren($d->createElement('br'), 'ook', $d->createElement('span'), 'eek');
        $this->assertSame('<body><br>ook<span></span>eek</body>', (string)$d->body);

        $d->body->replaceChildren('ook');
        $this->assertSame('<body>ook</body>', (string)$d->body);
    }


    /** @covers \MensBeam\HTML\DOM\ParentNode::walk */
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
            } ]
        ];
    }

    /**
     * @dataProvider provideWalkFailures
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\ParentNode::walk
     */
    public function testWalkFailures(\Closure $closure): void {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::CLOSURE_RETURN_TYPE_ERROR);
        $closure();
    }
}