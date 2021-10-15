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


/** @covers \MensBeam\HTML\DOM\Node */
class TestBaseNode extends \PHPUnit\Framework\TestCase {
    /** @covers \MensBeam\HTML\DOM\BaseNode::compareDocumentPosition */
    public function testCompareDocumentPosition(): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $d->body->innerHTML = '<header><h1>Ook</h1></header><main><h2 id="eek">Eek</h2><p>Ook <a href="ook">eek</a>, ook?</p></main><footer></footer>';
        $m = $d->getElementsByTagName('main')->item(0);
        $f = $d->getElementsByTagName('footer')->item(0);
        $h2Id = $d->getElementById('eek')->getAttributeNode('id');
        $aHref = $d->getElementsByTagName('a')->item(0)->getAttributeNode('href');

        $compareMainToBody = $m->compareDocumentPosition($d->body);
        $this->assertEquals(10, $compareMainToBody);
        $compareBodyToMain = $d->body->compareDocumentPosition($m);
        $this->assertEquals(20, $compareBodyToMain);
        $compareFooterToMain = $f->compareDocumentPosition($m);
        $this->assertEquals(2, $compareFooterToMain);
        $compareMainToFooter = $m->compareDocumentPosition($f);
        $this->assertEquals(4, $compareMainToFooter);
        $this->assertEquals(0, $m->compareDocumentPosition($m));

        $this->assertGreaterThan(0, $compareMainToBody & Document::DOCUMENT_POSITION_CONTAINS);
        $this->assertGreaterThan(0, $compareMainToBody & Document::DOCUMENT_POSITION_PRECEDING);
        $this->assertEquals(0, $compareMainToBody & Document::DOCUMENT_POSITION_FOLLOWING);

        $this->assertGreaterThan(0, $compareBodyToMain & Document::DOCUMENT_POSITION_CONTAINED_BY);
        $this->assertGreaterThan(0, $compareBodyToMain & Document::DOCUMENT_POSITION_FOLLOWING);
        $this->assertEquals(0, $compareBodyToMain & Document::DOCUMENT_POSITION_PRECEDING);

        $this->assertGreaterThan(0, $compareFooterToMain & Document::DOCUMENT_POSITION_PRECEDING);
        $this->assertGreaterThan(0, $compareMainToFooter & Document::DOCUMENT_POSITION_FOLLOWING);

        $m->parentNode->removeChild($m);
        $compareDetachedMainToFooter = $m->compareDocumentPosition($f);
        $this->assertEquals($compareDetachedMainToFooter, $m->compareDocumentPosition($f));
        $this->assertGreaterThanOrEqual(35, $compareDetachedMainToFooter);
        $this->assertLessThanOrEqual(37, $compareDetachedMainToFooter);
        $this->assertNotEquals(36, $compareDetachedMainToFooter);
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
     * @covers \MensBeam\HTML\DOM\BaseNode::C14N
     * @covers \MensBeam\HTML\DOM\BaseNode::C14NFile
     * @covers \MensBeam\HTML\DOM\BaseNode::getLineNo
     */
    public function testDisabledMethods(string $methodName, ...$arguments): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::NOT_SUPPORTED);
        $d = new Document();
        $d->$methodName(...$arguments);
    }


    /** @covers \MensBeam\HTML\DOM\BaseNode::getRootNode */
    public function testGetRootNode(): void {
        $d = new Document();
        $t = $d->createElement('template');
        $div = $t->content->appendChild($d->createElement('div'));
        $this->assertTrue($t->content->isSameNode($div->getRootNode()));
    }
}