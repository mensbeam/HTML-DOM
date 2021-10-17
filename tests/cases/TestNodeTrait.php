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
    Exception
};


/** @covers \MensBeam\HTML\DOM\NodeTrait */
class TestNodeTrait extends \PHPUnit\Framework\TestCase {
    /** @covers \MensBeam\HTML\DOM\NodeTrait::compareDocumentPosition */
    public function testCompareDocumentPosition(): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $d->body->innerHTML = '<header><h1>Ook</h1></header><main><h2 id="eek" class="ack">Eek</h2><p>Ook <a href="ook">eek</a>, ook?</p></main><footer></footer>';
        $m = $d->getElementsByTagName('main')->item(0);
        $f = $d->getElementsByTagName('footer')->item(0);
        $e = $d->getElementById('eek');
        $h2Id = $e->getAttributeNode('id');
        $h2Class = $e->getAttributeNode('class');
        $aHref = $d->getElementsByTagName('a')->item(0)->getAttributeNode('href');

        $compareMainToBody = $m->compareDocumentPosition($d->body);
        $this->assertEquals(10, $compareMainToBody);
        $compareBodyToMain = $d->body->compareDocumentPosition($m);
        $this->assertEquals(20, $compareBodyToMain);
        $compareFooterToMain = $f->compareDocumentPosition($m);
        $this->assertEquals(2, $compareFooterToMain);
        $compareMainToFooter = $m->compareDocumentPosition($f);
        $this->assertEquals(4, $compareMainToFooter);
        $compareH2IdToAHref = $h2Id->compareDocumentPosition($aHref);
        $this->assertEquals(4, $compareH2IdToAHref);
        $compareH2IdToH2Class = $h2Id->compareDocumentPosition($h2Class);
        $this->assertEquals(36, $compareH2IdToH2Class);
        $compareH2ClassToH2Id = $h2Class->compareDocumentPosition($h2Id);
        $this->assertEquals(34, $compareH2ClassToH2Id);
        $this->assertEquals(0, $m->compareDocumentPosition($m));

        $this->assertGreaterThan(0, $compareMainToBody & Document::DOCUMENT_POSITION_CONTAINS);
        $this->assertGreaterThan(0, $compareMainToBody & Document::DOCUMENT_POSITION_PRECEDING);
        $this->assertEquals(0, $compareMainToBody & Document::DOCUMENT_POSITION_FOLLOWING);

        $this->assertGreaterThan(0, $compareBodyToMain & Document::DOCUMENT_POSITION_CONTAINED_BY);
        $this->assertGreaterThan(0, $compareBodyToMain & Document::DOCUMENT_POSITION_FOLLOWING);
        $this->assertEquals(0, $compareBodyToMain & Document::DOCUMENT_POSITION_PRECEDING);

        $this->assertGreaterThan(0, $compareFooterToMain & Document::DOCUMENT_POSITION_PRECEDING);
        $this->assertGreaterThan(0, $compareMainToFooter & Document::DOCUMENT_POSITION_FOLLOWING);

        $this->assertGreaterThan(0, $compareH2IdToAHref & Document::DOCUMENT_POSITION_FOLLOWING);
        $this->assertGreaterThan(0, $compareH2IdToH2Class & Document::DOCUMENT_POSITION_FOLLOWING);
        $this->assertGreaterThan(0, $compareH2ClassToH2Id & Document::DOCUMENT_POSITION_PRECEDING);

        $m->parentNode->removeChild($m);
        $compareDetachedMainToFooter = $m->compareDocumentPosition($f);
        $this->assertEquals($compareDetachedMainToFooter, $m->compareDocumentPosition($f));
        $this->assertGreaterThanOrEqual(35, $compareDetachedMainToFooter);
        $this->assertLessThanOrEqual(37, $compareDetachedMainToFooter);
        $this->assertNotEquals(36, $compareDetachedMainToFooter);
    }


    /** @covers \MensBeam\HTML\DOM\NodeTrait::contains */
    public function testContains(): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $o = $d->body->appendChild($d->createTextNode('Ook!'));
        $d2 = new Document();
        $d2->appendChild($d2->createElement('html'));

        $this->assertTrue($d->documentElement->contains($d->body));
        $this->assertTrue($d->contains($o));
        $this->assertFalse($o->contains($d));
        $this->assertFalse($d->contains($d2->documentElement));
    }


    public function provideDisabledMethods(): iterable {
        return [
            [ 'C14N' ],
            [ 'C14NFile', 'ook' ],
            [ 'getLineNo' ]
        ];
    }

    /**
     * @dataProvider provideDisabledMethods
     * @covers \MensBeam\HTML\DOM\NodeTrait::C14N
     * @covers \MensBeam\HTML\DOM\NodeTrait::C14NFile
     * @covers \MensBeam\HTML\DOM\NodeTrait::getLineNo
     */
    public function testDisabledMethods(string $methodName, ...$arguments): void {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::DISABLED_METHOD);
        $d = new Document();
        $d->$methodName(...$arguments);
    }


    /** @covers \MensBeam\HTML\DOM\NodeTrait::getRootNode */
    public function testGetRootNode(): void {
        $d = new Document();
        $t = $d->createElement('template');
        $div = $t->content->appendChild($d->createElement('div'));
        $this->assertTrue($t->content->isSameNode($div->getRootNode()));
    }

    /** @covers \MensBeam\HTML\DOM\NodeTrait::isEqualNode */
    public function testIsEqualNode(): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $d->body->innerHTML = '<main><h1>Ook</h1><p>Eek</p></main><footer></footer>';

        $d2 = new Document();
        $d2->appendChild($d2->createElement('html'));
        $d2->documentElement->appendChild($d2->createElement('body'));
        $d2->body->innerHTML = '<main><h1>Ook</h1><p>Eek</p></main><footer></footer>';

        $this->assertTrue($d->isEqualNode($d2));

        $d = new Document();
        $de = $d->createElement('html');
        $this->assertFalse($d->isEqualNode($de));

        $d = new Document();
        $d->appendChild($d->implementation->createDocumentType('html', '', ''));

        $d2 = new Document();
        $d2->appendChild($d2->implementation->createDocumentType('ook', 'eek', 'ack'));
        $this->assertFalse($d->isEqualNode($d2));

        $d = new Document('<!DOCTYPE html><html lang="en"><head><title>Ook!</title></head><body><head><h1>Eek</h1></head><footer></footer></body></html>');
        $d2 = new Document('<!DOCTYPE html><html lang="en"><head><title>Eek!</title></head><body><head><h1>Eek</h1></head><footer></footer></body></html>');
        $this->assertFalse($d->isEqualNode($d2));

        $d = new Document();
        $f = $d->createDocumentFragment();
        $f->appendChild($d->createElement('span'));
        $f->appendChild($d->createTextNode('Ook'));

        $f2 = $d->createDocumentFragment();
        $f2->appendChild($d->createElement('span'));
        $this->assertFalse($f->isEqualNode($f2));

        $s = $d->createElement('span');
        $s->setAttribute('id', 'ook');
        $s2 = $d->createElement('span');
        $s2->setAttribute('class', 'ook');
        $this->assertFalse($s->isEqualNode($s2));

        $s = $d->createElement('span');
        $br = $d->createElement('br');
        $this->assertFalse($s->isEqualNode($br));
    }
}