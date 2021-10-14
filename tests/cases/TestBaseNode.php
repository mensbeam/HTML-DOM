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