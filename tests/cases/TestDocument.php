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
    DOMImplementation,
    Element,
    HTMLElement,
    Node,
    Text,
    XMLDocument
};
use MensBeam\HTML\DOM\Inner\Reflection,
    org\bovigo\vfs\vfsStream;


/** @covers \MensBeam\HTML\DOM\Document */
class TestDocument extends \PHPUnit\Framework\TestCase {
    /**
     * @covers \MensBeam\HTML\DOM\Document::offsetExists
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     */
    public function testMethod_offsetExists(): void {
        $d = new Document('<!DOCTYPE html><html><body><img name="ook"></body></html>');
        $this->assertTrue(isset($d['ook']));
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::offsetExists
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     */
    public function testMethod_offsetExists__errors(): void {
        // PHPUnit is supposed to support expecting of errors, but it doesn't. So let's
        // write a bunch of bullshit so we can catch and assert errors instead.
        set_error_handler(function($errno) {
            if ($errno === \E_USER_ERROR) {
                $this->assertEquals(\E_USER_ERROR, $errno);
            }
        });

        $d = new Document();
        isset($d[0]);

        restore_error_handler();
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::offsetGet
     *
     * @covers \MensBeam\HTML\DOM\Collection::__construct
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
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
    public function testMethod_offsetGet(): void {
        $d = new Document('<!DOCTYPE html><html><body><img name="ook"><img name="eek"><img id="eek" name="ack"><embed name="eek"><object id="ook"><embed name="eek"><object name="ookeek"></object></object><iframe name="eek"></iframe><object id="eek"></object></body></html>');
        $this->assertSame(HTMLElement::class, $d['ook']::class);
        $this->assertEquals(5, $d['eek']->length);
        $this->assertNull($d['ookeek']);

        $d['ook'] = 'fail';
        $this->assertSame(HTMLElement::class, $d['ook']::class);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::offsetGet
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     */
    public function testMethod_offsetGet__errors(): void {
        // PHPUnit is supposed to support expecting of errors, but it doesn't. So let's
        // write a bunch of bullshit so we can catch and assert errors instead.
        set_error_handler(function($errno) {
            if ($errno === \E_USER_ERROR) {
                $this->assertEquals(\E_USER_ERROR, $errno);
            }
        });

        $d = new Document();
        $fail = $d[0];

        restore_error_handler();
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::offsetSet
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\Document::offsetGet
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
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
    public function testMethod_offsetSet(): void {
        $d = new Document('<!DOCTYPE html><html><body><img name="ook"></body></html>');
        $d['ook'] = 'fail';
        $this->assertSame(HTMLElement::class, $d['ook']::class);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::offsetUnset
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\Document::offsetGet
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
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
    public function testMethod_offsetUnset(): void {
        $d = new Document('<!DOCTYPE html><html><body><img name="ook"></body></html>');
        unset($d['ook']);
        $this->assertSame(HTMLElement::class, $d['ook']::class);
    }

    /**
     * @covers \MensBeam\HTML\DOM\Document::adoptNode
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createDocumentFragment
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::importNode
     * @covers \MensBeam\HTML\DOM\DocumentFragment::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\HTMLTemplateElement::__construct
     * @covers \MensBeam\HTML\DOM\HTMLTemplateElement::__get_content
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::__get_parentNode
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::appendChildInner
     * @covers \MensBeam\HTML\DOM\Node::cloneInnerNode
     * @covers \MensBeam\HTML\DOM\Node::cloneWrapperNode
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\Node::removeChild
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::delete
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::setProtectedProperties
     */
    public function testMethod_adoptNode(): void {
        $d = new Document();
        $documentElement = $d->appendChild($d->createElement('html'));
        $body = $documentElement->appendChild($d->createElement('body'));
        $template = $body->appendChild($d->createElement('template'));
        $d2 = new Document();

        $d2->adoptNode($documentElement);
        $this->assertSame($d2, $documentElement->ownerDocument);

        $d2->adoptNode($template->content);
        $this->assertSame($d, $template->content->ownerDocument);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::adoptNode
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
    public function testMethod_adoptNode__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::NOT_SUPPORTED);
        $d = new Document();
        $d2 = new Document();
        $d2->adoptNode($d);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::createAttribute
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
    public function testMethod_createAttribute__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::INVALID_CHARACTER);
        $d = new Document();
        $d->createAttribute('this will fail');
    }


    public function provideMethod_createCDATASection__errors(): iterable {
        return [
            [ function () {
                $d = new Document();
                $d->createCDATASection('ook');
            }, DOMException::NOT_SUPPORTED ],
            [ function () {
                $d = new XMLDocument();
                $d->createCDATASection('ook]]>');
            }, DOMException::INVALID_CHARACTER ],
        ];
    }

    /**
     * @dataProvider provideMethod_createCDATASection__errors
     * @covers \MensBeam\HTML\DOM\Document::createCDATASection
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
    public function testMethod_createCDATASection__errors(\Closure $closure, int $errorCode): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode($errorCode);
        $closure();
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::createElement
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
    public function testMethod_createElement__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::INVALID_CHARACTER);
        $d = new Document();
        $d->createElement('this will fail');
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::destroy
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::delete
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     */
    public function testMethod_destroy(): void {
        $d = new Document();

        $reflection = new \ReflectionClass(Document::class);
        $cache = $reflection->getStaticPropertyValue('cache');
        $innerArrayCount = count(Reflection::getProtectedProperty($cache, 'innerArray'));
        $wrapperArrayCount = count(Reflection::getProtectedProperty($cache, 'wrapperArray'));

        $d->destroy();

        $cache = $reflection->getStaticPropertyValue('cache');
        $this->assertNotEquals(count(Reflection::getProtectedProperty($cache, 'innerArray')), $innerArrayCount);
        $this->assertNotEquals(count(Reflection::getProtectedProperty($cache, 'wrapperArray')), $wrapperArrayCount);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::getElementsByName
     *
     * @covers \MensBeam\HTML\DOM\Collection::__construct
     * @covers \MensBeam\HTML\DOM\Collection::__get_length
     * @covers \MensBeam\HTML\DOM\Collection::count
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testMethod_getElementsByName() {
        $d = new Document('<!DOCTYPE html><html><body><div name="ook"></div><div name="ook"></div><svg><g name="ook"></g></svg></body></html>');
        $this->assertEquals(2, $d->getElementsByName('ook')->length);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::importNode
     *
     * @covers \MensBeam\HTML\DOM\CharacterData::__get_data
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createCDATASection
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_firstChild
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::cloneInnerNode
     * @covers \MensBeam\HTML\DOM\Node::cloneWrapperNode
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\Text::__construct
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
    public function testMethod_importNode(): void {
        $d = new Document();
        $d2 = new XMLDocument();
        $d3 = new \DOMDocument();

        // Test importing of PHP DOM node
        $div = $d3->createElement('div');
        $div = $d->importNode($div);
        $this->assertTrue($div instanceof Element);

        // Test importing of CDATA section node
        $cdata = $d2->createCDATASection('ook');
        $cdata = $d->importNode($cdata);
        $this->assertSame(Text::class, $cdata::class);

        // Test importing of element containing CDATA section node
        $div = $d2->createElement('div');
        $div->appendChild($d2->createCDATASection('ook'));
        $div = $d->importNode($div, true);
        $this->assertSame(Text::class, $div->firstChild::class);
    }


    public function provideMethod_importNode__errors(): iterable {
        return [
            [ function () {
                $d = new Document();
                $d->importNode(new Document());
            } ],
            [ function () {
                $d = new Document();
                $d->importNode(new \DOMDocument());
            } ],
            [ function () {
                $d = new Document();
                $d2 = new class extends \DOMDocument {};
                $d2->createTextNode('fail');
                $d->importNode($d2);
            } ],
            [ function () {
                $d = new Document();
                $d2 = new \DOMDocument();
                $d2->createEntityReference('nbsp');
                $d->importNode($d2);
            } ],
        ];
    }

    /**
     * @dataProvider provideMethod_importNode__errors
     * @covers \MensBeam\HTML\DOM\Document::importNode
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
    public function testMethod_importNode__errors(\Closure $closure): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::NOT_SUPPORTED);
        $closure();
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::load
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     */
    public function testMethod_load__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::NO_MODIFICATION_ALLOWED);
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->load('this will fail');
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::loadFile
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_charset
     * @covers \MensBeam\HTML\DOM\Document::__get_URL
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
    public function testMethod_loadFile(): void {
        $d = new Document();

        $vfs = vfsStream::setup('DOM', 0777, [ 'test.html' => <<<HTML
        <!DOCTYPE html>
        <html>
         <head>
          <meta charset="ISO-2022-JP">
          <title>Ook</title>
         </head>
        </html>
        HTML ]);
        $f = $vfs->url() . '/test.html';

        // Test loading of virtual file
        $d->loadFile($f);
        $this->assertSame('ISO-2022-JP', $d->charset);
        $this->assertStringStartsWith('vfs://', $d->URL);

        // Test loading of local file
        $d = new Document();
        $d->loadFile(dirname(__FILE__) . '/../test.html', 'UTF-8');
        $this->assertSame('ISO-2022-JP', $d->charset);
        $this->assertStringStartsWith('file://', $d->URL);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::loadFile
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
    public function testMethod_loadFile__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::FILE_NOT_FOUND);
        $d = new Document();
        $d->loadFile('fail.html');
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::serialize
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testMethod_serialize__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::WRONG_DOCUMENT);
        $d = new XMLDocument();
        $d2 = new Document();
        $d2->serialize($d->createTextNode('ook'));
    }

    /**
     * @covers \MensBeam\HTML\DOM\Document::serializeInner
     * 
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testMethod_serializeInner(): void {
        $d = new Document();
        $e = $d->createElement('p');
        $e->textContent = 'ook';

        $this->assertSame('ook', $d->serializeInner($e));
    }

    /**
     * @covers \MensBeam\HTML\DOM\Document::serializeInner
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testMethod_serializeInner__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::WRONG_DOCUMENT);
        $d = new XMLDocument();
        $d2 = new Document();
        $d2->serializeInner($d->createTextNode('ook'));
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
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
    public function testProperty_body() {
        $d = new Document();

        // Document::body without document element
        $this->assertNull($d->body);

        $d->appendChild($d->createElement('html'));

        // Document::body without body
        $this->assertNull($d->body);

        $body = $d->documentElement->appendChild($d->createElement('body'));

        // Document::body with body
        $this->assertSame($body, $d->body);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__get_charset
     * @covers \MensBeam\HTML\DOM\Document::__get_inputEncoding
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
    public function testProperty_charset() {
        $d = new Document('<!DOCTYPE html><html><head><meta charset="gb2312"></head></html>');
        $this->assertSame('GBK', $d->charset);
        $this->assertSame('GBK', $d->inputEncoding);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__get_dir
     * @covers \MensBeam\HTML\DOM\Document::__set_dir
     *
     * @covers \MensBeam\HTML\DOM\Attr::__get_value
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_localName
     * @covers \MensBeam\HTML\DOM\Element::__get_namespaceURI
     * @covers \MensBeam\HTML\DOM\Element::__get_prefix
     * @covers \MensBeam\HTML\DOM\Element::__get_tagName
     * @covers \MensBeam\HTML\DOM\Element::getAttribute
     * @covers \MensBeam\HTML\DOM\Element::getAttributeNode
     * @covers \MensBeam\HTML\DOM\Element::setAttribute
     * @covers \MensBeam\HTML\DOM\HTMLElement::__get_dir
     * @covers \MensBeam\HTML\DOM\HTMLElement::__set_dir
     * @covers \MensBeam\HTML\DOM\Node::__construct
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
     */
    public function testProperty_dir(): void {
        $d = new Document('<!DOCTYPE html><html dir="ltr"></html>');
        $this->assertSame('ltr', $d->dir);
        $d->dir = 'bullshit';
        $this->assertSame('', $d->dir);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__get_doctype
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_implementation
     * @covers \MensBeam\HTML\DOM\DocumentType::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createDocumentType
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::setProtectedProperties
     */
    public function testProperty_doctype() {
        $d = new Document();
        $this->assertNull($d->doctype);

        $doctype = $d->appendChild($d->implementation->createDocumentType('html', '', ''));
        $this->assertSame($doctype, $d->doctype);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__get_documentURI
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\Document::loadFile
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::cloneInnerNode
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     */
    public function testProperty_documentURI() {
        $d = new Document();
        $d->loadFile('https://google.com');
        $this->assertSame('https://google.com', $d->documentURI);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__get_embeds
     *
     * @covers \MensBeam\HTML\DOM\Collection::__construct
     * @covers \MensBeam\HTML\DOM\Collection::__get_length
     * @covers \MensBeam\HTML\DOM\Collection::count
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_plugins
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testProperty_embeds() {
        $d = new Document('<!DOCTYPE html><html><body><embed></embed><embed></embed><embed></embed><div><div><div><embed></embed></div></div></div></body></html>');
        $this->assertEquals(4, $d->embeds->length);
        $this->assertEquals(4, $d->plugins->length);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__get_forms
     *
     * @covers \MensBeam\HTML\DOM\Collection::__construct
     * @covers \MensBeam\HTML\DOM\Collection::__get_length
     * @covers \MensBeam\HTML\DOM\Collection::count
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createDocumentFragment
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DocumentFragment::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\HTMLTemplateElement::__construct
     * @covers \MensBeam\HTML\DOM\HTMLTemplateElement::__get_content
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::appendChildInner
     * @covers \MensBeam\HTML\DOM\Node::cloneInnerNode
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
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
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::setProtectedProperties
     */
    public function testProperty_forms() {
        $d = new Document('<!DOCTYPE html><html><body><form></form><form></form><form></form><div><div><div><form></form></div></div></div><template><form></form></template></body></html>');
        $this->assertEquals(4, $d->forms->length);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__get_head
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
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
    public function testProperty_head() {
        $d = new Document();
        $this->assertNull($d->head);

        $de = $d->appendChild($d->createElement('html'));
        $head = $de->appendChild($d->createElement('head'));

        $this->assertSame($head, $d->head);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__get_images
     *
     * @covers \MensBeam\HTML\DOM\Collection::__construct
     * @covers \MensBeam\HTML\DOM\Collection::__get_length
     * @covers \MensBeam\HTML\DOM\Collection::count
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createDocumentFragment
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DocumentFragment::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\HTMLTemplateElement::__construct
     * @covers \MensBeam\HTML\DOM\HTMLTemplateElement::__get_content
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::appendChildInner
     * @covers \MensBeam\HTML\DOM\Node::cloneInnerNode
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
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
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::setProtectedProperties
     */
    public function testProperty_images() {
        $d = new Document('<!DOCTYPE html><html><body><img><img><img><div><div><div><img></div></div></div><template><img></template></body></html>');
        $this->assertEquals(4, $d->images->length);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__get_links
     *
     * @covers \MensBeam\HTML\DOM\Collection::__construct
     * @covers \MensBeam\HTML\DOM\Collection::__get_length
     * @covers \MensBeam\HTML\DOM\Collection::count
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createDocumentFragment
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DocumentFragment::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\HTMLTemplateElement::__construct
     * @covers \MensBeam\HTML\DOM\HTMLTemplateElement::__get_content
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::appendChildInner
     * @covers \MensBeam\HTML\DOM\Node::cloneInnerNode
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
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
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::setProtectedProperties
     */
    public function testProperty_links() {
        $d = new Document('<!DOCTYPE html><html><body><a href=""></a><a href=""></a><a href=""></a><div><div><div><area href=""></area></div></div></div><template><a href=""></a></template></body></html>');
        $this->assertEquals(4, $d->links->length);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__get_scripts
     *
     * @covers \MensBeam\HTML\DOM\Collection::__construct
     * @covers \MensBeam\HTML\DOM\Collection::__get_length
     * @covers \MensBeam\HTML\DOM\Collection::count
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createDocumentFragment
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DocumentFragment::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\HTMLTemplateElement::__construct
     * @covers \MensBeam\HTML\DOM\HTMLTemplateElement::__get_content
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::appendChildInner
     * @covers \MensBeam\HTML\DOM\Node::cloneInnerNode
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
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
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::setProtectedProperties
     */
    public function testProperty_scripts() {
        $d = new Document('<!DOCTYPE html><html><body><script></script><script></script><script></script><div><div><div><script></script></div></div></div><template><script></script></template></body></html>');
        $this->assertEquals(4, $d->scripts->length);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__get_title
     * @covers \MensBeam\HTML\DOM\Document::__set_title
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createElementNS
     * @covers \MensBeam\HTML\DOM\DocumentOrElement::validateAndExtract
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::createDocument
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_namespaceURI
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_innerNode
     * @covers \MensBeam\HTML\DOM\Node::__get_ownerDocument
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::setProtectedProperties
     */
    public function testProperty_title() {
        $d = new Document();
        $this->assertSame('', $d->title);
        $d->title = 'fail';
        $this->assertSame('', $d->title);

        $d = (new DOMImplementation)->createDocument(Node::SVG_NAMESPACE, 'svg');
        $this->assertSame('', $d->title);

        $d->title = 'Ook';
        $this->assertSame('Ook', $d->title);
        $d->title = '   Ee  k  ';
        $this->assertSame('Ee k', $d->title);

        $d = new Document();
        $de = $d->appendChild($d->createElement('html'));
        $d->title = 'Ook';
        $this->assertSame('', $d->title);

        $de->appendChild($d->createElement('head'));
        $d->title = 'Ook';
        $this->assertSame('Ook', $d->title);
        $d->title = 'Eek';
        $this->assertSame('Eek', $d->title);
    }
}