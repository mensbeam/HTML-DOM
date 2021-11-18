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
    Element,
    Node,
    Text,
    XMLDocument
};


/** @covers \MensBeam\HTML\DOM\Element */
class TestElement extends \PHPUnit\Framework\TestCase {
    /**
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNames
     *
     * @covers \MensBeam\HTML\DOM\Collection::__construct
     * @covers \MensBeam\HTML\DOM\Collection::item
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DocumentOrElement::getElementsByTagName
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\HTMLCollection::item
     * @covers \MensBeam\HTML\DOM\HTMLCollection::offsetGet
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testMethod_getAttributeNames() {
        $d = new Document('<!DOCTYPE html><html><body><div id="ook" class="eek" ack="ack" foo="foo" bar="bar"></div></body></html>');
        $div = $d->getElementsByTagName('div')[0];
        $this->assertSame([ 'id', 'class', 'ack', 'foo', 'bar' ], $div->getAttributeNames());
    }


    public function testMethod_getAttributeNS() {
        $d = new Document('<!DOCTYPE html><html><head></head><body><svg xmlns="' . Node::SVG_NAMESPACE . '" xmlns:xlink="' . Node::XLINK_NAMESPACE . '"></svg></body></html>');
        $svg = $d->getElementsByTagNameNS(Node::SVG_NAMESPACE, 'svg')[0];
        // Parser doesn't parse xmlns prefixed attributes except xlink, so let's add one manually instead to test coercion.
        $svg->setAttributeNS(Node::XMLNS_NAMESPACE, 'xmlns:poop💩', 'https://poop💩.poop');

        $this->assertSame(Node::SVG_NAMESPACE, $svg->getAttributeNS(Node::XMLNS_NAMESPACE, 'xmlns'));
        $this->assertSame(Node::XLINK_NAMESPACE, $svg->getAttributeNS(Node::XMLNS_NAMESPACE, 'xlink'));
        $this->assertSame('https://poop💩.poop', $svg->getAttributeNS(Node::XMLNS_NAMESPACE, 'poop💩'));
    }
}