<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\TestCase;

use MensBeam\HTML\DOM\{
    DOMException,
    Element,
    XMLDocument
};


/** @covers \MensBeam\HTML\DOM\XMLDocument */
class TestXMLDocument extends \PHPUnit\Framework\TestCase {
    public function testMethod_load(): void {
        $d = new XMLDocument('<ook><eek>Ook</eek></ook>');
        $this->assertSame(Element::class, $d->firstChild::class);
    }


    public function testMethod_load__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::NO_MODIFICATION_ALLOWED);
        $d = new XMLDocument('<ook><eek>Ook</eek></ook>');
        $d->load('fail');
    }
}