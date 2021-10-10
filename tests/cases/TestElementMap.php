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
    ElementMap
};


/**
 * @covers \MensBeam\HTML\DOM\ElementMap
 */
class TestElementMap extends \PHPUnit\Framework\TestCase {
    /** @covers \MensBeam\HTML\DOM\ElementMap::add */
    public function testAdd(): void {
        $d = new Document();
        $t = $d->createElement('template');
        $this->assertTrue(ElementMap::add($t));
        $this->assertFalse(ElementMap::add($t));
    }


    /** @covers \MensBeam\HTML\DOM\ElementMap::delete */
    public function testDelete(): void {
        $d = new Document();
        $t = $d->createElement('template');
        $this->assertTrue(ElementMap::add($t));
        $this->assertTrue(ElementMap::delete($t));
        $this->assertFalse(ElementMap::delete($t));
    }
}