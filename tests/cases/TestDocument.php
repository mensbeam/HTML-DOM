<?php
/** @license MIT
 * Copyright 2017 , Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\TestCase;

use MensBeam\HTML\DOM\{
    Document,
    DOMException,
    Element,
    Exception,
    HTMLTemplateElement
};
use MensBeam\HTML\Parser;


class TestDocument extends \PHPUnit\Framework\TestCase {
     /**
      * @covers \MensBeam\HTML\DOM\Document::__construct
      * @covers \MensBeam\HTML\DOM\Document::loadDOM
      * @covers \MensBeam\HTML\DOM\Document::loadHTML
      */
     public function testDocumentCreation(): void {
         // Test null source
         $d = new Document();
         $this->assertSame('MensBeam\HTML\DOM\Document', $d::class);
         $this->assertSame(null, $d->firstChild);

         // Test string source
         $d = new Document('<html><body>Ook!</body></html>');
         $this->assertSame(Parser::QUIRKS_MODE, $d->quirksMode);

         // Test DOM source
         $d = new \DOMDocument();
         $d->appendChild($d->createElement('html'));
         $d = new Document($d);
         $this->assertSame('MensBeam\HTML\DOM\Element', $d->firstChild::class);
         $this->assertSame('html', $d->firstChild->nodeName);
     }
}
