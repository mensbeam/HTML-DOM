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
    DOMException,
    HTMLElement,
    HTMLPreElement,
    HTMLUnknownElement
};


/** @covers \MensBeam\HTML\DOM\Inner\Document */
class TestInnerDocument extends \PHPUnit\Framework\TestCase {
    /**
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testMethod_getWrapperNode(): void {
        // Everything tests this method thoroughly except some element interfaces.
        $d = new Document();
        $this->assertSame(HTMLUnknownElement::class, $d->createElement('applet')::class);
        $this->assertSame(HTMLElement::class, $d->createElement('noembed')::class);
        $this->assertSame(HTMLPreElement::class, $d->createElement('xmp')::class);
        $this->assertSame(HTMLPreElement::class, $d->createElement('pre')::class);
        $this->assertSame(HTMLElement::class, $d->createElement('p-icon')::class);
    }


    public function provideMethod_getWrapperNode__errors(): iterable {
        return [
            [ function () {
                $d = new Document();
                $d->innerNode->getWrapperNode(new \DOMDocument());
            }, DOMException::NOT_SUPPORTED ],
            [ function () {
                $d = new Document();
                $d2 = new Document();
                $d->innerNode->getWrapperNode($d2->innerNode->createTextNode('fail'));
            }, DOMException::WRONG_DOCUMENT ],
        ];
    }

    /**
     * @dataProvider provideMethod_getWrapperNode__errors
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     */
    public function testMethod_getWrapperNode__errors(\Closure $closure, int $errorCode): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode($errorCode);
        $closure();
    }
}