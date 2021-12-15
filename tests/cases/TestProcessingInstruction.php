<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\TestCase;

use MensBeam\HTML\DOM\Document;


/** @covers \MensBeam\HTML\DOM\ProcessingInstruction */
class TestProcessingInstruction extends \PHPUnit\Framework\TestCase {
    public function testProperty_target(): void {
        $d = new Document();
        $this->assertSame('ook', $d->createProcessingInstruction('ook', 'eek')->target);
        $this->assertSame('poop💩', $d->createProcessingInstruction('poop💩', 'poop💩')->target);
    }
}