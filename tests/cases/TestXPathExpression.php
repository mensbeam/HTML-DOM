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
    Node,
    XPathException,
    XPathExpression,
    XPathResult
};


/** @covers \MensBeam\HTML\DOM\XPathExpression */
class TestXPathExpression extends \PHPUnit\Framework\TestCase {
    /**
     * @covers \MensBeam\HTML\DOM\XPathExpression::__construct
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
     * @covers \MensBeam\HTML\DOM\DocumentOrElement::validateAndExtract
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNodeNS
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNode
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNodeNS
     * @covers \MensBeam\HTML\DOM\Element::setAttributeNS
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::locateNamespace
     * @covers \MensBeam\HTML\DOM\Node::lookupNamespaceURI
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\XPathEvaluatorBase::createExpression
     * @covers \MensBeam\HTML\DOM\XPathEvaluatorBase::createNSResolver
     * @covers \MensBeam\HTML\DOM\XPathNSResolver::__construct
     * @covers \MensBeam\HTML\DOM\XPathNSResolver::lookupNamespaceURI
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
    function testMethod_constructor(): void {
        $d = new Document('<!DOCTYPE><html></html>');
        $d->documentElement->setAttributeNS(Node::XMLNS_NAMESPACE, 'xmlns:poop', 'https://poop.poop');
        $poop = $d->body->appendChild($d->createElementNS('https://poop.poop', 'poop:poop'));

        $this->assertSame(XPathExpression::class, $d->createExpression('//poop:poop', $d->createNSResolver($d))::class);
        $this->assertSame(XPathExpression::class, $d->createExpression('//span')::class);
    }


    function provideMethod_constructor__errors(): iterable {
        return [
            [ function() {
                $d = new Document();
                $d->createExpression('//fail:fail');
            },
            XPathException::UNRESOLVABLE_NAMESPACE_PREFIX ],

            [ function() {
                $d = new Document();
                $d->createExpression('//fail:fail', $d->createNSResolver($d));
            },
            XPathException::UNRESOLVABLE_NAMESPACE_PREFIX ],

            [ function() {
                $d = new Document();
                $d->createExpression('fail?');
            },
            XPathException::INVALID_EXPRESSION ],

            [ function() {
                $d = new Document();
                $d->createExpression('fail?', $d->createNSResolver($d));
            },
            XPathException::INVALID_EXPRESSION ],
        ];
    }


    /**
     * @dataProvider provideMethod_constructor__errors
     * @covers \MensBeam\HTML\DOM\XPathExpression::__construct
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\XPathEvaluate::xpathErrorHandler
     * @covers \MensBeam\HTML\DOM\XPathEvaluatorBase::createExpression
     * @covers \MensBeam\HTML\DOM\XPathException::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    function testMethod_constructor__errors(\Closure $closure, int $errorCode): void {
        $this->expectException(XPathException::class);
        $this->expectExceptionCode($errorCode);
        $closure();
    }


    /**
     * @covers \MensBeam\HTML\DOM\XPathExpression::evaluate
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\XPathEvaluate::xpathEvaluate
     * @covers \MensBeam\HTML\DOM\XPathEvaluatorBase::createExpression
     * @covers \MensBeam\HTML\DOM\XPathExpression::__construct
     * @covers \MensBeam\HTML\DOM\XPathResult::__construct
     * @covers \MensBeam\HTML\DOM\XPathResult::__get_booleanValue
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     */
    function testMethod_evaluate(): void {
        $d = new Document('<!DOCTYPE html><html><body><span><span>Ook</span></span><span></span></body></html>');
        $e = $d->createExpression('//span');
        $this->assertTrue($e->evaluate($d, XPathResult::BOOLEAN_TYPE)->booleanValue);
    }
}