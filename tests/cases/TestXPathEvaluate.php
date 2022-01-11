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
    Node,
    XPathException,
    XPathResult
};


/** @covers \MensBeam\HTML\DOM\XPathEvaluate */
class TestXPathEvaluate extends \PHPUnit\Framework\TestCase {
    /**
     * @covers \MensBeam\HTML\DOM\XPathEvaluate::xpathEvaluate
     *
     * @covers \MensBeam\HTML\DOM\Attr::__get_localName
     * @covers \MensBeam\HTML\DOM\Attr::__get_namespaceURI
     * @covers \MensBeam\HTML\DOM\Attr::__get_ownerElement
     * @covers \MensBeam\HTML\DOM\Attr::__set_value
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::createAttributeNS
     * @covers \MensBeam\HTML\DOM\Document::createElementNS
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\Document::registerXPathFunctions
     * @covers \MensBeam\HTML\DOM\DocumentOrElement::validateAndExtract
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNodeNS
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNode
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNodeNS
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNS
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::locateNamespace
     * @covers \MensBeam\HTML\DOM\Node::lookupNamespaceURI
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\XPathEvaluate::xpathRegisterPhpFunctions
     * @covers \MensBeam\HTML\DOM\XPathEvaluatorBase::createNSResolver
     * @covers \MensBeam\HTML\DOM\XPathEvaluatorBase::evaluate
     * @covers \MensBeam\HTML\DOM\XPathNSResolver::__construct
     * @covers \MensBeam\HTML\DOM\XPathResult::__construct
     * @covers \MensBeam\HTML\DOM\XPathResult::__get_booleanValue
     * @covers \MensBeam\HTML\DOM\XPathResult::__get_numberValue
     * @covers \MensBeam\HTML\DOM\XPathResult::__get_singleNodeValue
     * @covers \MensBeam\HTML\DOM\XPathResult::__get_stringValue
     * @covers \MensBeam\HTML\DOM\XPathResult::count
     * @covers \MensBeam\HTML\DOM\XPathResult::validateStorage
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
    function testMethod_xpathEvaluate(): void {
        $d = new Document('<!DOCTYPE html><html><body><span><span>Ook</span></span><span></span></body></html>');
        $d->registerXPathFunctions();
        $result = $d->evaluate('.//span', $d->body);
        $this->assertEquals(3, count($result));

        $result = $d->evaluate('count(.//span)', $d->body, null, XPathResult::NUMBER_TYPE);
        $this->assertEquals(3, $result->numberValue);
        $result = $d->evaluate('count(.//span)', $d->body, null);
        $this->assertEquals(3, $result->numberValue);
        $result = $d->evaluate('name(.//span)', $d->body, null, XPathResult::STRING_TYPE);
        $this->assertEquals('span', $result->stringValue);
        $result = $d->evaluate('name(.//span)', $d->body, null);
        $this->assertEquals('span', $result->stringValue);
        $result = $d->evaluate('.//span', $d->body, null, XPathResult::BOOLEAN_TYPE);
        $this->assertTrue($result->booleanValue);
        $result = $d->evaluate('not(.//span)', $d->body, null, XPathResult::BOOLEAN_TYPE);
        $this->assertFalse($result->booleanValue);
        $result = $d->evaluate('not(.//span)', $d->body, null);
        $this->assertFalse($result->booleanValue);
        $result = $d->evaluate('.//span', $d->body, null, XPathResult::ORDERED_NODE_ITERATOR_TYPE);
        $this->assertEquals(3, count($result));
        $result = $d->evaluate('.//span', $d->body, null);
        $this->assertEquals(3, count($result));
        $result = $d->evaluate('.//span', $d->body, null, XPathResult::UNORDERED_NODE_SNAPSHOT_TYPE);
        $this->assertEquals(3, count($result));
        $result = $d->evaluate('.//span', $d->body, null, XPathResult::FIRST_ORDERED_NODE_TYPE);
        $this->assertSame($d->body->firstChild, $result->singleNodeValue);

        $d->documentElement->setAttributeNS(Node::XMLNS_NAMESPACE, 'xmlns:poop', 'https://poop.poop');
        $poop = $d->body->appendChild($d->createElementNS('https://poop.poop', 'poop:poop'));
        $this->assertSame($poop, $d->evaluate('//poop:poop', $d->body, $d->createNSResolver($d->body), XPathResult::FIRST_ORDERED_NODE_TYPE)->singleNodeValue);
    }


    function provideMethod_xpathEvaluate__errors(): iterable {
        return [
            [ function() {
                $d = new Document();
                $d->evaluate('fail?', $d);
            },
            XPathException::class,
            XPathException::INVALID_EXPRESSION ],

            [ function() {
                $d = new Document();
                $d->evaluate('//fail', $d, null, XPathResult::NUMBER_TYPE);
            },
            XPathException::class,
            XPathException::TYPE_ERROR ],

            [ function() {
                $d = new Document();
                $d->evaluate('//fail', $d, null, XPathResult::STRING_TYPE);
            },
            XPathException::class,
            XPathException::TYPE_ERROR ],

            [ function() {
                $d = new Document();
                $d->evaluate('count(//fail)', $d, null, XPathResult::UNORDERED_NODE_ITERATOR_TYPE);
            },
            XPathException::class,
            XPathException::TYPE_ERROR ],

            [ function() {
                $d = new Document();
                $d->evaluate('count(//fail)', $d, null, XPathResult::ORDERED_NODE_SNAPSHOT_TYPE);
            },
            XPathException::class,
            XPathException::TYPE_ERROR ],

            [ function() {
                $d = new Document();
                $d->evaluate('count(//fail)', $d, null, XPathResult::ANY_UNORDERED_NODE_TYPE);
            },
            XPathException::class,
            XPathException::TYPE_ERROR ],

            [ function() {
                $d = new Document();
                $d->evaluate('//svg:svg', $d, null);
            },
            XPathException::class,
            XPathException::UNRESOLVABLE_NAMESPACE_PREFIX ],

            [ function() {
                $d = new Document();
                $d->evaluate('count(//fail)', $d, null, 2112);
            },
            DOMException::class,
            DOMException::NOT_SUPPORTED ]
        ];
    }

    /**
     * @dataProvider provideMethod_xpathEvaluate__errors
     * @covers \MensBeam\HTML\DOM\XPathEvaluate::xpathEvaluate
     *
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\XPathEvaluatorBase::evaluate
     * @covers \MensBeam\HTML\DOM\XPathException::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     */
    function testMethod_xpathEvaluate__errors(\Closure $closure, string $errorClass, int $errorCode): void {
        $this->expectException($errorClass);
        $this->expectExceptionCode($errorCode);
        $closure();
    }
}