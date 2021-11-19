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
     * @covers \MensBeam\HTML\DOM\Attr::__get_value
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_namespaceURI
     * @covers \MensBeam\HTML\DOM\Element::getAttribute
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNode
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testMethod_getAttribute() {
        $d = new Document('<!DOCTYPE html><html id="ook" poop💩="jeff"></html>', 'UTF-8');
        $documentElement = $d->documentElement;

        // id attribute
        $this->assertSame('ook', $documentElement->getAttribute('id'));
        // coerced attribute
        $this->assertSame('jeff', $documentElement->getAttribute('poop💩'));
        // nonexistent attribute
        $this->assertNull($documentElement->getAttribute('class'));
    }

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


    /**
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNodeNS
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNS
     *
     * @covers \MensBeam\HTML\DOM\Attr::__get_value
     * @covers \MensBeam\HTML\DOM\Attr::__set_value
     * @covers \MensBeam\HTML\DOM\Collection::__construct
     * @covers \MensBeam\HTML\DOM\Collection::item
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::createAttributeNS
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DocumentOrElement::getElementsByTagNameNS
     * @covers \MensBeam\HTML\DOM\DocumentOrElement::validateAndExtract
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNode
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNodeNS
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNS
     * @covers \MensBeam\HTML\DOM\HTMLCollection::item
     * @covers \MensBeam\HTML\DOM\HTMLCollection::offsetGet
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     */
    public function testMethod_getAttributeNS() {
        $d = new Document('<!DOCTYPE html><html><head></head><body><svg xmlns="' . Node::SVG_NAMESPACE . '" xmlns:xlink="' . Node::XLINK_NAMESPACE . '" viewBox="0 0 42 42"></svg></body></html>');
        $svg = $d->getElementsByTagNameNS(Node::SVG_NAMESPACE, 'svg')[0];
        // Parser per the spec doesn't parse xmlns prefixed attributes except xlink, so let's add one manually instead to test coercion.
        $svg->setAttributeNS(Node::XMLNS_NAMESPACE, 'xmlns:poop💩', 'https://poop💩.poop');

        // xmlns attribute
        $this->assertSame(Node::SVG_NAMESPACE, $svg->getAttributeNS(Node::XMLNS_NAMESPACE, 'xmlns'));
        // xmlns xlink attribute
        $this->assertSame(Node::XLINK_NAMESPACE, $svg->getAttributeNS(Node::XMLNS_NAMESPACE, 'xlink'));
        // coerced namespaced attribute
        $this->assertSame('https://poop💩.poop', $svg->getAttributeNS(Node::XMLNS_NAMESPACE, 'poop💩'));
        // nonexistent namespaced attribute
        $this->assertNull($svg->getAttributeNS(Node::XMLNS_NAMESPACE, 'ook'));
        // empty string namespace
        $this->assertSame('0 0 42 42', $svg->getAttributeNS('', 'viewBox'));
    }
}