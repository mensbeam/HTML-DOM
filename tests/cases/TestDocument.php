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
    ElementMap,
    Exception,
    HTMLTemplateElement
};
use MensBeam\HTML\Parser,
    org\bovigo\vfs\vfsStream;


/** @covers \MensBeam\HTML\DOM\Document */
class TestDocument extends \PHPUnit\Framework\TestCase {
    /** @covers \MensBeam\HTML\DOM\Document::adoptNode */
    public function testAdoptNode() {
        $d = new Document();
        $t = $d->createElement('template');

        $d2 = new Document();
        $t2 = $d2->adoptNode($t, true);
        $this->assertSame($d2, $t2->ownerDocument);
        $this->assertNull($t->parentNode);

        /*$d = new \DOMDocument();
        $t = $d->createElement('template');
        // Add a child template to cover recursive template conversions.
        $t->appendChild($d->createElement('template'));
        $this->assertSame(\DOMElement::class, $t::class);

        $d2 = new Document();
        $t2 = $d2->importNode($t, true);
        $this->assertSame(HTMLTemplateElement::class, $t2::class);*/
    }

    public function provideAttributeNodeCreation(): iterable {
        return [
            [ 'test',      'test' ],
            [ 'TEST',      'test' ],
            [ 'test:test', 'testU00003Atest' ],
            [ 'TEST:TEST', 'testU00003Atest' ]
        ];
    }

    /**
     * @dataProvider provideAttributeNodeCreation
     * @covers \MensBeam\HTML\DOM\Document::createAttribute
     */
    public function testAttributeNodeCreation(string $nameIn, string $local): void {
        // Test without a document element and with
        $d = new Document();
        $a = $d->createAttribute($nameIn);
        $this->assertSame($local, $a->localName);

        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $a = $d->createAttribute($nameIn);
        $this->assertSame($local, $a->localName);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::createAttribute
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     */
    public function testAttributeNodeCreationFailure(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::INVALID_CHARACTER);
        $d = new Document();
        $d->createAttribute('<ook>');
    }


    public function provideAttributeNodeNSCreation(): iterable {
        return [
            [ 'fake_ns',         'test',             'fake_ns',         '',     'test' ],
            [ 'fake_ns',         'test:test',        'fake_ns',         'test', 'test' ],
            [ 'fake_ns',         'TEST:TEST',        'fake_ns',         'TEST', 'TEST' ],
            [ 'another_fake_ns', 'steaming💩:poop💩', 'another_fake_ns', 'steamingU01F4A9', 'poopU01F4A9' ],
            // An empty string for a prefix is technically incorrect, but we cannot fix that.
            [ '',                'poop💩',            null,              '',                'poopU01F4A9' ],
            // An empty string for a prefix is technically incorrect, but we cannot fix that.
            [ null,              'poop💩',            null,              '',                'poopU01F4A9' ]
        ];
    }

    /**
     * @dataProvider provideAttributeNodeNSCreation
     * @covers \MensBeam\HTML\DOM\Document::createAttributeNS
     * @covers \MensBeam\HTML\DOM\Document::validateAndExtract
     */
    public function testAttributeNodeNSCreation(?string $nsIn, string $nameIn, ?string $nsExpected, ?string $prefixExpected, string $localNameExpected): void {
        // Test without a document element and with
        $d = new Document();
        $a = $d->createAttributeNS($nsIn, $nameIn);
        $this->assertSame($nsExpected, $a->namespaceURI);
        $this->assertSame($prefixExpected, $a->prefix);
        $this->assertSame($localNameExpected, $a->localName);

        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $a = $d->createAttributeNS($nsIn, $nameIn);
        $this->assertSame($nsExpected, $a->namespaceURI);
        $this->assertSame($prefixExpected, $a->prefix);
        $this->assertSame($localNameExpected, $a->localName);
    }


    public function provideDisabledMethods(): iterable {
        return [
            [ 'createCDATASection', 'ook' ],
            [ 'createEntityReference', 'ook' ],
            [ 'loadXML', 'ook' ],
            [ 'registerNodeClass', 'ook', null ],
            [ 'relaxNGValidate', 'ook' ],
            [ 'relaxNGValidateSource', 'ook' ],
            [ 'saveXML', null ],
            [ 'schemaValidate', 'ook', 0 ],
            [ 'schemaValidateSource', 'ook', 0 ],
            [ 'validate', null ],
            [ 'xinclude', null ],
        ];
    }

    /**
     * @dataProvider provideDisabledMethods
     * @covers \MensBeam\HTML\DOM\Document::createEntityReference
     * @covers \MensBeam\HTML\DOM\Document::loadXML
     * @covers \MensBeam\HTML\DOM\Document::saveXML
     * @covers \MensBeam\HTML\DOM\Document::validate
     * @covers \MensBeam\HTML\DOM\Document::xinclude
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     */
    public function testDisabledMethods(string $methodName, ...$arguments): void {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::DISABLED_METHOD);
        $d = new Document();
        $d->$methodName(...$arguments);
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::convertTemplate
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\Document::loadDOM
     * @covers \MensBeam\HTML\DOM\Document::loadHTML
     * @covers \MensBeam\HTML\DOM\Document::loadHTMLFile
     * @covers \MensBeam\HTML\DOM\Document::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\Document::replaceTemplates
     * @covers \MensBeam\HTML\DOM\Document::__get_compatMode
     * @covers \MensBeam\HTML\DOM\Document::__get_URL
     * @covers \MensBeam\HTML\DOM\NodeTrait::getRootNode
     */
    public function testDocumentCreation(): void {
        // Test null source
        $d = new Document();
        $this->assertSame('MensBeam\HTML\DOM\Document', get_class($d));
        $this->assertSame(null, $d->firstChild);

        // Test compatibility mode
        $d = new Document('<html><body>Ook!</body></html>');
        $this->assertSame('BackCompat', $d->compatMode);

        // Test DOM source
        $d = new \DOMDocument();
        $d->appendChild($d->createElement('html'));
        $d2 = new Document();
        $d2->appendChild($d2->createElement('html'));
        $d2->loadDOM($d);
        $d3 = new Document($d);
        $this->assertSame('MensBeam\HTML\DOM\Element', get_class($d3->firstChild));
        $this->assertSame('html', $d3->firstChild->nodeName);

        // Test file source
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

        // Test nonexistent file source
        $d = new Document();
        $this->assertFalse(@$d->load('fileDoesNotExist.html'));
        $d->load($f);
        $this->assertNotNull($d->documentElement);
        $this->assertSame('ISO-2022-JP', $d->charset);

        // Test http source
        $d = new Document();
        $d->load('https://google.com');
        $this->assertNotNull($d->documentElement);
        $this->assertSame('UTF-8', $d->charset);
        $this->assertSame('https://google.com', $d->URL);
        $this->assertNull($d->documentURI);

        // Test document encoding
        $d = new Document();
        $d->loadHTMLFile($f, null, 'UTF-8');
        $this->assertSame('UTF-8', $d->charset);

        // Test real document loading
        $d = new Document();
        $d->loadHTMLFile(dirname(__FILE__) . '/../test.html', null, 'UTF-8');
        $this->assertStringStartsWith('file://', $d->URL);

        // Test templates in source
        $d = new Document('<!DOCTYPE html><html><body><template class="test"><template></template></template></body></html>');
        $t = $d->getElementsByTagName('template')->item(0);
        $this->assertSame(HTMLTemplateElement::class, get_class($t));
        $this->assertSame(HTMLTemplateElement::class, get_class($t->content->firstChild));
    }


    public function provideElementCreation(): iterable {
        return [
            // HTML element
            [ 'div',         'div',      Element::class ],
            // HTML element and uppercase qualified name
            [ 'DIV',         'div',      Element::class ],
            // Template element
            [ 'template',    'template', HTMLTemplateElement::class ],
            // Template element and uppercase qualified name
            [ 'TEMPLATE',    'template', HTMLTemplateElement::class ],
            // Name coercion
            [ 'poop💩',       'poopU01F4A9',   Element::class ]
        ];
    }

    /**
     * @dataProvider provideElementCreation
     * @covers \MensBeam\HTML\DOM\Document::createElement
     */
    public function testElementCreation(string $nameIn, string $nameExpected, string $classExpected): void {
        $d = new Document;
        $n = $d->createElement($nameIn);
        $this->assertInstanceOf($classExpected, $n);
        $this->assertNotNull($n->ownerDocument);
        $this->assertSame($nameExpected, $n->nodeName);
    }


    public function provideElementCreationFailures(): iterable {
        return [
            [ function() {
                $d = new Document();
                $d->createElement('ook', 'FAIL');
            }, DOMException::NOT_SUPPORTED ],
            [ function() {
                $d = new Document();
                $d->createElement('<ook>');
            }, DOMException::INVALID_CHARACTER ]
        ];
    }


    /**
     * @dataProvider provideElementCreationFailures
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     */
    public function testElementCreationFailures(\Closure $closure, int $errorCode): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode($errorCode);
        $closure();
    }


    public function provideElementCreationNS(): iterable {
        return [
            // HTML element with a null namespace
            [ null,                   null,                   'div',      'div',           Element::class ],
            // Template element with a null namespace
            [ null,                   null,                   'template', 'template',      HTMLTemplateElement::class ],
            // Template element with a null namespace and uppercase name
            [ null,                   null,                   'TEMPLATE', 'TEMPLATE',      HTMLTemplateElement::class ],
            // Template element
            [ Parser::HTML_NAMESPACE, Parser::HTML_NAMESPACE, 'template', 'template',      HTMLTemplateElement::class ],
            // SVG element with SVG namespace
            [ Parser::SVG_NAMESPACE,  Parser::SVG_NAMESPACE,  'svg',      'svg',           Element::class ],
            // SVG element with SVG namespace and uppercase local name
            [ Parser::SVG_NAMESPACE,  Parser::SVG_NAMESPACE,  'SVG',      'SVG',           Element::class ],
            // Name coercion
            [ 'steaming💩',           'steaming💩',            'poop💩',   'poopU01F4A9',   Element::class ]
        ];
    }

    /**
     * @dataProvider provideElementCreationNS
     * @covers \MensBeam\HTML\DOM\Document::createElementNS
     * @covers \MensBeam\HTML\DOM\Document::validateAndExtract
     */
    public function testElementCreationNS(?string $nsIn, ?string $nsExpected, string $localNameIn, string $localNameExpected, string $classExpected): void {
        $d = new Document();
        $n = $d->createElementNS($nsIn, $localNameIn);
        $this->assertInstanceOf($classExpected, $n);
        $this->assertNotNull($n->ownerDocument);
        $this->assertSame($nsExpected, $n->namespaceURI);
        $this->assertSame($localNameExpected, $n->localName);
    }


    public function provideElementCreationNSFailures(): iterable {
        return [
            [ function() {
                $d = new Document();
                $d->createElementNS('ook', 'ook', 'FAIL');
            }, DOMException::NOT_SUPPORTED ],
            [ function() {
                $d = new Document();
                $d->createElementNS(null, '<ook>');
            }, DOMException::INVALID_CHARACTER ],
            [ function() {
                $d = new Document();
                $d->createElementNS(null, 'xmlns');
            }, DOMException::NAMESPACE_ERROR ]
        ];
    }

    /**
     * @dataProvider provideElementCreationNSFailures
     * @covers \MensBeam\HTML\DOM\Document::createElementNS
     * @covers \MensBeam\HTML\DOM\Document::validateAndExtract
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     */
    public function testElementCreationNSFailures(\Closure $closure, int $errorCode): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode($errorCode);
        $closure();
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::save
     * @covers \MensBeam\HTML\DOM\Document::saveHTMLFile
     */
    public function testFileSaving(): void {
        $vfs = vfsStream::setup('DOM', 0777);
        $path = $vfs->url() . '/test.html';
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->save($path);
        $this->assertSame('<html></html>', file_get_contents($path));
        $d->saveHTMLFile($path);
        $this->assertSame('<html></html>', file_get_contents($path));
    }


    /** @covers \MensBeam\HTML\DOM\Document::importNode */
    public function testImportNode() {
        $d = new Document();
        $t = $d->createElement('template');

        $d2 = new Document();
        $t2 = $d2->importNode($t, true);
        $this->assertFalse($t2->ownerDocument->isSameNode($t->ownerDocument));
        $this->assertSame($t2::class, $t::class);

        $d = new \DOMDocument();
        $t = $d->createElement('template');
        // Add a child template to cover recursive template conversions.
        $t->appendChild($d->createElement('template'));
        $this->assertSame(\DOMElement::class, $t::class);

        $d2 = new Document();
        $t2 = $d2->importNode($t, true);
        $this->assertSame(HTMLTemplateElement::class, $t2::class);
    }


    /** @covers \MensBeam\HTML\DOM\Document::importNode */
    public function testImportingNodesFailure() {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::NOT_SUPPORTED);
        $d = new \DOMDocument();
        $c = $d->createCDATASection('fail');
        $d2 = new Document();
        $d2->importNode($c);
    }


    /** @covers \MensBeam\HTML\DOM\Document::__get_body */
    public function testPropertyGetBody(): void {
        $d = new Document();
        $this->assertNull($d->body);
        $d->appendChild($d->createElement('html'));
        $this->assertNull($d->body);
        $d->documentElement->appendChild($d->createTextNode(' '));
        $this->assertNull($d->body);
        $f = $d->createElement('frameset');
        $d->documentElement->appendChild($f);
        $this->assertNotNull($d->body);
        $d->documentElement->removeChild($f);
    }


    /** @covers \MensBeam\HTML\DOM\Document::__set_body */
    public function testPropertySetBody(): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $b = $d->createElement('body');
        $d->body = $b;
        $this->assertSame('body', $d->body->nodeName);
        $d->body = $b;
        $this->assertSame('body', $d->body->nodeName);

        $b = $d->createElement('body');
        $b->appendChild($d->createTextNode('Ook'));
        $d->body = $b;
        $this->assertSame('Ook', $d->body->firstChild->data);
    }

    public function providePropertySetBodyFailures(): iterable {
        $result = [];
        $d = new Document();
        $result[] = [ $d, $d->createElement('body') ];
        $d = new Document();
        $result[] = [ $d, $d->createElement('div') ];
        $d = new Document();
        $result[] = [ $d, $d->createTextNode('FAIL') ];
        return $result;
    }

    /**
     * @dataProvider providePropertySetBodyFailures
     * @covers \MensBeam\HTML\DOM\Document::__set_body
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     */
    public function testPropertySetBodyFailures(Document $document, \DOMNode $node): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::HIERARCHY_REQUEST_ERROR);
        $document->body = $node;
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__get_charset
     * @covers \MensBeam\HTML\DOM\Document::__get_characterSet
     * @covers \MensBeam\HTML\DOM\Document::__get_inputEncoding
     */
    public function testPropertyGetCharset(): void {
        $d = new Document(null, 'UTF-8');
        $this->assertSame('UTF-8', $d->charset);
        $this->assertSame('UTF-8', $d->characterSet);
        $this->assertSame('UTF-8', $d->inputEncoding);

        $d = new Document('<!DOCTYPE html><html><head><meta charset="GB18030"></head></html>');
        $this->assertSame('gb18030', $d->charset);
        $this->assertSame('gb18030', $d->characterSet);
        $this->assertSame('gb18030', $d->inputEncoding);
    }


    public function providePropertyGetCompatMode(): iterable {
        return [
            // Empty document
            [ null,                           'CSS1Compat' ],
            // Document without doctype
            [ '<html></html>',                'BackCompat' ],
            // Document with doctype
            [ '<!DOCTYPE html><html></html>', 'CSS1Compat' ]
        ];
    }

    /**
     * @dataProvider providePropertyGetCompatMode
     * @covers \MensBeam\HTML\DOM\Document::__get_compatMode
     */
    public function testPropertyGetCompatMode(?string $html, string $compatMode): void {
        $d = new Document($html);
        $this->assertSame($compatMode, $d->compatMode);
    }


    /** @covers \MensBeam\HTML\DOM\Document::__get_contentType */
    public function testPropertyGetContentType(): void {
        $d = new Document();
        $this->assertSame('text/html', $d->contentType);
    }


    /** @covers \MensBeam\HTML\DOM\Document::__get_xpath */
    public function testPropertyGetXPath(): void {
        $d = new Document();
        $this->assertSame('DOMXPath', get_class($d->xpath));
    }


    /**
     * @covers \MensBeam\HTML\DOM\Document::__destruct
     * @covers \MensBeam\HTML\DOM\ElementMap::add
     * @covers \MensBeam\HTML\DOM\ElementMap::delete
     * @covers \MensBeam\HTML\DOM\ElementMap::destroy
     * @covers \MensBeam\HTML\DOM\ElementMap::has
     * @covers \MensBeam\HTML\DOM\ElementMap::index
     */
    public function testTemplateElementReferences(): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $t = $d->createElement('template');
        $this->assertFalse(ElementMap::has($t));
        $d->documentElement->appendChild($t);
        $this->assertTrue(ElementMap::has($t));
        $d->__destruct();
        $this->assertFalse(ElementMap::has($t));

        $d = new Document();
        $d->appendChild($d->createElement('html'));

        $t = $d->importNode($t);
        $this->assertFalse(ElementMap::has($t));
        $d->documentElement->appendChild($t);
        $this->assertTrue(ElementMap::has($t));
        $d->documentElement->removeChild($t);
        $this->assertFalse(ElementMap::has($t));
    }
}