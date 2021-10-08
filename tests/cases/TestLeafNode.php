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
    DOMException
};


/** @covers \MensBeam\HTML\DOM\LeafNode */
class TestLeafNode extends \PHPUnit\Framework\TestCase {
    public function provideDisabledMethods(): iterable {
        return [
            [ function($d, $n) {
                $n->appendChild($d->createElement('fail'));
            } ],
            [ function($d, $n) {
                $n->insertBefore($d->createElement('fail'));
            } ],
            [ function($d, $n) {
                $n->removeChild($d->createElement('fail'));
            } ],
            [ function($d, $n) {
                $n->replaceChild($d->createElement('fail2'), $d->createElement('fail'));
            } ],
        ];
    }

    /**
     * @dataProvider provideDisabledMethods
     * @covers \MensBeam\HTML\DOM\LeafNode::appendChild
     * @covers \MensBeam\HTML\DOM\LeafNode::insertBefore
     * @covers \MensBeam\HTML\DOM\LeafNode::removeChild
     * @covers \MensBeam\HTML\DOM\LeafNode::replaceChild
     */
    public function testDisabledMethods(\Closure $closure): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::HIERARCHY_REQUEST_ERROR);
        $d = new Document();
        $closure($d, $d->createTextNode('ook'));
    }
}