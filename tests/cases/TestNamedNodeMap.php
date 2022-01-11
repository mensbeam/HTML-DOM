<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\TestCase;

use MensBeam\HTML\DOM\{
    Attr,
    Document,
    DOMException,
    Node,
    XMLDocument
};


/** @covers \MensBeam\HTML\DOM\NamedNodeMap */
class TestNamedNodeMap extends \PHPUnit\Framework\TestCase {
    /**
     * @covers \MensBeam\HTML\DOM\NamedNodeMap::current
     * @covers \MensBeam\HTML\DOM\NamedNodeMap::item
     * @covers \MensBeam\HTML\DOM\NamedNodeMap::offsetExists
     *
     * @covers \MensBeam\HTML\DOM\Attr::__get_localName
     * @covers \MensBeam\HTML\DOM\Attr::__get_namespaceURI
     * @covers \MensBeam\HTML\DOM\Attr::__get_ownerElement
     * @covers \MensBeam\HTML\DOM\Attr::__set_value
     * @covers \MensBeam\HTML\DOM\Collection::current
     * @covers \MensBeam\HTML\DOM\Collection::item
     * @covers \MensBeam\HTML\DOM\Collection::key
     * @covers \MensBeam\HTML\DOM\Collection::next
     * @covers \MensBeam\HTML\DOM\Collection::rewind
     * @covers \MensBeam\HTML\DOM\Collection::valid
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::createAttributeNS
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DocumentOrElement::validateAndExtract
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_attributes
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNodeNS
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNode
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNodeNS
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNS
     * @covers \MensBeam\HTML\DOM\NamedNodeMap::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     */
    public function testIteration(): void {
        $d = new Document('<!DOCTYPE html><html><body a="ook" b="eek" c="ook" d="eek" e="ook" poop💩="poop💩"></body></html>');
        $body = $d->body;

        $body->setAttributeNS(Node::XMLNS_NAMESPACE, 'xmlns:href', Node::HTML_NAMESPACE);
        $body->setAttributeNS('https://poop💩.poop', 'poop💩:poop💩', 'poop💩');

        $attributes = $body->attributes;
        foreach ($attributes as $key => $attr) {
            $this->assertTrue($attr instanceof Attr);
        }
    }


    /**
     * @covers \MensBeam\HTML\DOM\NamedNodeMap::getNamedItem
     * @covers \MensBeam\HTML\DOM\NamedNodeMap::getNamedItemNS
     * @covers \MensBeam\HTML\DOM\NamedNodeMap::offsetGet
     *
     * @covers \MensBeam\HTML\DOM\Attr::__get_localName
     * @covers \MensBeam\HTML\DOM\Attr::__get_namespaceURI
     * @covers \MensBeam\HTML\DOM\Attr::__get_ownerElement
     * @covers \MensBeam\HTML\DOM\Attr::__get_value
     * @covers \MensBeam\HTML\DOM\Attr::__set_value
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::createAttribute
     * @covers \MensBeam\HTML\DOM\Document::createAttributeNS
     * @covers \MensBeam\HTML\DOM\Document::importNode
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DocumentOrElement::validateAndExtract
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_attributes
     * @covers \MensBeam\HTML\DOM\Element::__get_namespaceURI
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNode
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNodeNS
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNode
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNodeNS
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNS
     * @covers \MensBeam\HTML\DOM\NamedNodeMap::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::__get_nodeName
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::cloneInnerNode
     * @covers \MensBeam\HTML\DOM\Node::cloneWrapperNode
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     */
    public function testMethod_getNamedItem_getNamedItemNS_offsetGet(): void {
        $d = new Document('<!DOCTYPE html><html><body a="ook" b="eek" c="ook" d="eek" e="ook" poop💩="poop💩"></body></html>', 'UTF-8');
        $body = $d->body;

        $body->setAttributeNS(Node::XMLNS_NAMESPACE, 'xmlns:href', Node::HTML_NAMESPACE);
        $body->setAttributeNS('https://poop💩.poop', 'poop💩:poop💩', 'poop💩');

        $attributes = $body->attributes;
        $this->assertSame($attributes[0], $attributes['a']);
        $this->assertSame('ook', $attributes['a']->value);
        $this->assertSame('ook', $attributes->getNamedItem('a')->value);
        $this->assertSame(Node::HTML_NAMESPACE, $attributes['xmlns:href']->value);
        $this->assertSame('poop💩', $attributes['poop💩']->value);
        $this->assertSame('poop💩', $attributes->getNamedItem('poop💩')->value);
        $this->assertSame('poop💩', $attributes['poop💩:poop💩']->value);
        $this->assertSame('poop💩', $attributes->getNamedItemNS('https://poop💩.poop', 'poop💩')->value);

        // Testing an edge case with uppercased attributes in the specification...
        $d2 = new XMLDocument();
        $F = $d2->createAttribute('F');
        $F->value = 'eek';
        $F = $d->importNode($F);
        $body->setAttributeNode($F);
        $this->assertNull($attributes['F']);
        $this->assertSame('F', $F->nodeName);
    }


    /**
     * @covers \MensBeam\HTML\DOM\NamedNodeMap::removeNamedItem
     * @covers \MensBeam\HTML\DOM\NamedNodeMap::removeNamedItemNS
     *
     * @covers \MensBeam\HTML\DOM\Attr::__get_localName
     * @covers \MensBeam\HTML\DOM\Attr::__get_namespaceURI
     * @covers \MensBeam\HTML\DOM\Attr::__get_ownerElement
     * @covers \MensBeam\HTML\DOM\Attr::__set_value
     * @covers \MensBeam\HTML\DOM\Collection::__get_length
     * @covers \MensBeam\HTML\DOM\Collection::count
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::createAttributeNS
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DocumentOrElement::validateAndExtract
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_attributes
     * @covers \MensBeam\HTML\DOM\Element::__get_namespaceURI
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNode
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNodeNS
     * @covers \MensBeam\HTML\DOM\Element::hasAttribute
     * @covers \MensBeam\HTML\DOM\Element::hasAttributeNS
     * @covers \MensBeam\HTML\DOM\Element::removeAttributeNode
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNode
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNodeNS
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNS
     * @covers \MensBeam\HTML\DOM\NamedNodeMap::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     */
    public function testMethod_removeNamedItem_removeNamedItemNS(): void {
        $d = new Document('<!DOCTYPE html><html><body a="ook" b="eek" c="ook" d="eek" e="ook" poop💩="poop💩"></body></html>', 'UTF-8');
        $body = $d->body;

        $body->setAttributeNS(Node::XMLNS_NAMESPACE, 'xmlns:href', Node::HTML_NAMESPACE);
        $body->setAttributeNS('https://poop💩.poop', 'poop💩:poop💩', 'poop💩');

        $attributes = $body->attributes;
        $this->assertEquals(8, $attributes->length);
        $attributes->removeNamedItem('a');
        $this->assertEquals(7, $attributes->length);
        $this->assertFalse($body->hasAttribute('a'));
        $attributes->removeNamedItem('poop💩');
        $this->assertEquals(6, $attributes->length);
        $this->assertFalse($body->hasAttribute('poop💩'));
        $attributes->removeNamedItemNS('https://poop💩.poop', 'poop💩');
        $this->assertEquals(5, $attributes->length);
        $this->assertFalse($body->hasAttributeNS('https://poop💩.poop', 'poop💩'));
    }


    /**
     * @covers \MensBeam\HTML\DOM\NamedNodeMap::removeNamedItem
     * @covers \MensBeam\HTML\DOM\NamedNodeMap::removeNamedItemNS
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_attributes
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNodeNS
     * @covers \MensBeam\HTML\DOM\NamedNodeMap::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testMethod_removeNamedItem__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::NOT_FOUND);
        $d = new Document('<!DOCTYPE html><html><body></body></html>', 'UTF-8');
        $body = $d->body;
        $body->attributes->removeNamedItem('fail');
    }


    /**
     * @covers \MensBeam\HTML\DOM\NamedNodeMap::setNamedItem
     * @covers \MensBeam\HTML\DOM\NamedNodeMap::setNamedItemNS
     *
     * @covers \MensBeam\HTML\DOM\Attr::__get_localName
     * @covers \MensBeam\HTML\DOM\Attr::__get_namespaceURI
     * @covers \MensBeam\HTML\DOM\Attr::__get_ownerElement
     * @covers \MensBeam\HTML\DOM\Attr::__set_value
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::createAttribute
     * @covers \MensBeam\HTML\DOM\Document::createAttributeNS
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DocumentOrElement::validateAndExtract
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_attributes
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNodeNS
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNode
     * @covers \MensBeam\HTML\DOM\NamedNodeMap::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     */
    public function testMethod_setNamedItem_setNamedItemNS(): void {
        $d = new Document('<!DOCTYPE html><html><body a="ook" b="eek" c="ook" d="eek" e="ook"></body></html>', 'UTF-8');
        $body = $d->body;
        $attributes = $body->attributes;

        $poop = $d->createAttribute('poop💩');
        $poop->value = 'poop💩';
        $attributes->setNamedItem($poop);
        $this->assertSame($attributes['poop💩'], $poop);

        $x = $d->createAttributeNS(Node::XMLNS_NAMESPACE, 'xmlns:href');
        $x->value = Node::HTML_NAMESPACE;
        $attributes->setNamedItemNS($x);
        $this->assertSame($attributes['xmlns:href'], $x);
    }
}