<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\TestCase;

use MensBeam\HTML\DOM\Document;


/** @covers \MensBeam\HTML\DOM\DocumentOrElement */
class TestDocumentOrElement extends \PHPUnit\Framework\TestCase {
    /** @covers \MensBeam\HTML\DOM\DocumentOrElement::getElementsByClassName */
    public function testGetElementsByClassName(): void {
        $d = new Document();
        $this->assertSame(0, $d->getElementsByClassName('fail')->length);
        $this->assertSame(0, $d->getElementsByClassName('')->length);
        $d->appendChild($d->createElement('html'));
        $d->documentElement->setAttribute('class', 'ook');
        $this->assertSame($d->documentElement->nodeName, $d->getElementsByClassName('ook')->item(0)->nodeName);
    }
}