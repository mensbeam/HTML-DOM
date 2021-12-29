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
    DOMException
};


/** @covers \MensBeam\HTML\DOM\HTMLOrSVGElement */
class TestHTMLOrSVGElement extends \PHPUnit\Framework\TestCase {
    public function testProperty_autofocus(): void {
        $d = new Document('<!DOCTYPE html><html></html>');
        $html = $d->documentElement;
        $this->assertFalse($html->autofocus);
        $html->autofocus = true;
        $this->assertTrue($html->autofocus);
        $html->autofocus = false;
        $this->assertFalse($html->autofocus);
        $this->assertFalse($html->hasAttribute('autofocus'));
    }
}