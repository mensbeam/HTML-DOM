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
    DocumentFragment,
    Element,
    Exception,
    HTMLTemplateElement
};


/** @covers \MensBeam\HTML\DOM\DocumentFragment */
class TestDocumentFragment extends \PHPUnit\Framework\TestCase {
    /** @covers \MensBeam\HTML\DOM\DocumentFragment::getElementById */
    public function testGetElementById(): void {
        $d = new Document();
        $df = $d->createDocumentFragment();
        $o = $df->appendChild($d->createElement('span'));
        $o->setAttribute('id', 'eek');
        $this->assertSame(Element::class, $df->getElementById('eek')::class);
        $this->assertNull($df->getElementById('ook'));
    }

    /** @covers \MensBeam\HTML\DOM\DocumentFragment::__get_host */
    public function testGetHost(): void {
        $d = new Document();
        // From a template
        $t = $d->createElement('template');
        $this->assertSame(HTMLTemplateElement::class, get_class($t->content->host));
        // From a created document fragment
        $df = $d->createDocumentFragment();
        $this->assertNull($df->host);
    }

    public function provideSetHostFailures(): iterable {
        return [
            [ function() {
                $d = new Document();
                $t = $d->createElement('template');
                $t->content->host = $d->createElement('template');
            } ],
            [ function() {
                $d = new Document();
                $df = $d->createDocumentFragment();
                $df->host = $d->createElement('template');
            } ]
        ];
    }

    /**
     * @dataProvider provideSetHostFailures
     * @covers \MensBeam\HTML\DOM\DocumentFragment::__set_host
     * @covers \MensBeam\HTML\DOM\Exception::__construct
     */
    public function testSetHostFailures(\Closure $closure): void {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::READONLY_PROPERTY);
        $closure();
    }
}