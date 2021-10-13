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
class TestNode extends \PHPUnit\Framework\TestCase {
    public function provideDisabledMethods(): iterable {
        return [
            [ function() {
                $d = new Document();
                $d->C14N();
            } ],
            [ function() {
                $d = new Document();
                $d->C14NFile('fail');
            } ],
        ];
    }

    /**
     * @dataProvider provideDisabledMethods
     * @covers \MensBeam\HTML\DOM\Node::C14N
     * @covers \MensBeam\HTML\DOM\Node::C14NFile
     */
    public function testDisabledMethods(\Closure $closure): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::NOT_SUPPORTED);
        $closure();
    }


    /** @covers \MensBeam\HTML\DOM\Node::getRootNode */
    public function testGetRootNode(): void {
        $d = new Document();
        $t = $d->createElement('template');
        $div = $t->content->appendChild($d->createElement('div'));
        $this->assertTrue($t->content->isSameNode($div->getRootNode()));
    }
}