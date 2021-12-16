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


/** @covers \MensBeam\HTML\DOM\Text */
class TestText extends \PHPUnit\Framework\TestCase {
    public function testMethod_splitText(): void {
        $d = new Document('<!DOCTYPE html><html><body>poop💩 ook eek</html>', 'UTF-8');
        $body = $d->body;
        $body->firstChild->splitText(5);
        $this->assertSame('poop💩', $body->firstChild->data);
    }


    public function testMethod_splitText__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::INDEX_SIZE_ERROR);
        $d = new Document('<!DOCTYPE html><html><body>poop💩 ook eek</html>', 'UTF-8');
        $body = $d->body;
        $body->firstChild->splitText(2112);
    }
    

    public function testProperty_wholeText(): void {
        $d = new Document('<!DOCTYPE html><html><body>ook <strong>ack</strong> eek</body></html>');
        $body = $d->body;
        $body->removeChild($body->getElementsByTagName('strong')[0]);
        $this->assertSame('ook  eek', $body->firstChild->wholeText);
    }
}