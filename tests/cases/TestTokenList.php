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
use MensBeam\HTML\Parser;


/** @covers \MensBeam\HTML\DOM\TokenList */
class TestTokenList extends \PHPUnit\Framework\TestCase {
    /** @covers \MensBeam\HTML\DOM\TokenList::__get_length */
    public function testPropertyGetLength(): void {
        $d = new Document();
        $e = $d->createElement('html');
        $e->classList->add('ook', 'eek', 'ack', 'ookeek');
        $this->assertSame(4, $e->classList->length);
    }
}