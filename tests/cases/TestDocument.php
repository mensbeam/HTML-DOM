<?php
/**
 * @license MIT
 * Copyright 2022 Dustin Wilson, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\Test;
use MensBeam\HTML\DOM\{
    CDataSection,
    Comment,
    Document,
    DocumentFragment,
    DOMImplementation,
    Element,
    FileNotFoundException,
    HTMLCollection,
    InvalidArgumentException,
    Node,
    ProcessingInstruction,
    XMLDocument,
    XPathException
};
use MensBeam\HTML\DOM\DOMException\{
    InvalidCharacterError,
    NoModificationAllowedError,
    NotSupportedError,
    WrongDocumentError,
};
use PHPUnit\Framework\{
    TestCase,
    Attributes\CoversClass,
    Attributes\DataProvider
};
use org\bovigo\vfs\vfsStream;


#[CoversClass('MensBeam\HTML\DOM\Document')]
#[CoversClass('MensBeam\HTML\DOM\Attr')]
#[CoversClass('MensBeam\HTML\DOM\Collection')]
#[CoversClass('MensBeam\HTML\DOM\DocumentFragment')]
#[CoversClass('MensBeam\HTML\DOM\DocumentOrElement')]
#[CoversClass('MensBeam\HTML\DOM\DocumentType')]
#[CoversClass('MensBeam\HTML\DOM\DOMImplementation')]
#[CoversClass('MensBeam\HTML\DOM\Element')]
#[CoversClass('MensBeam\HTML\DOM\FileNotFoundException')]
#[CoversClass('MensBeam\HTML\DOM\HTMLElement')]
#[CoversClass('MensBeam\HTML\DOM\HTMLTemplateElement')]
#[CoversClass('MensBeam\HTML\DOM\Node')]
#[CoversClass('MensBeam\HTML\DOM\Text')]
#[CoversClass('MensBeam\HTML\DOM\XMLDocument')]
#[CoversClass('MensBeam\HTML\DOM\XPathException')]
#[CoversClass('MensBeam\HTML\DOM\DOMException\InvalidCharacterError')]
#[CoversClass('MensBeam\HTML\DOM\DOMException\NoModificationAllowedError')]
#[CoversClass('MensBeam\HTML\DOM\DOMException\NotSupportedError')]
#[CoversClass('MensBeam\HTML\DOM\DOMException\WrongDocumentError')]
#[CoversClass('MensBeam\HTML\DOM\Inner\Document')]
#[CoversClass('MensBeam\HTML\DOM\Inner\NodeCache')]
#[CoversClass('MensBeam\HTML\DOM\Inner\Reflection')]
class TestDocument extends TestCase {
    public function testConstructor(): void {
        // Simple construction
        $d = new Document('<!DOCTYPE html><html></html>');
        $this->assertInstanceOf(Document::class, $d);
        $this->assertSame('CSS1Compat', $d->compatMode);
        $d->destroy();

        // Construction with charset
        $d = new Document('<!DOCTYPE html><html></html>', 'gb2312');
        $this->assertSame('GBK', $d->charset);
        $this->assertSame('GBK', $d->characterSet);
        $this->assertSame('GBK', $d->inputEncoding);
        $d->destroy();

        // Empty document with charset
        $d = new Document(charset: 'gb2312');
        $this->assertSame('GBK', $d->charset);
        $this->assertSame('GBK', $d->characterSet);
        $this->assertSame('GBK', $d->inputEncoding);
        $this->assertSame('CSS1Compat', $d->compatMode);
        $d->destroy();

        // Quirks mode
        $d = new Document('<doctype html><html><body></body></html>');
        $this->assertSame('BackCompat', $d->compatMode);
        $d->destroy();
    }

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
        $d->destroy();
        $d2->destroy();
    }

    public function testMethod_createAttribute(): void {
        // Attributes are lowercased in HTML documents
        $d = new Document('<!DOCTYPE html><html></html>');
        $a = $d->createAttribute('LANG');
        $this->assertSame('lang', $a->localName);
        $d->destroy();

        // They're not in XML documents
        $d = new XMLDocument('<ook></ook>');
        $a = $d->createAttribute('LANG');
        $this->assertSame('LANG', $a->localName);
        $d->destroy();

        // PHP normally can't create attributes if there's no document element, but
        // HTML-DOM can
        $d = new Document();
        $a = $d->createAttribute('LANG');
        $this->assertSame('lang', $a->localName);
        $this->assertSame($d, $a->ownerDocument);

        // PHP doesn't support unicode characters as attribute names, but HTML-DOM can
        // by internally replacing the characters
        $a = $d->createAttribute('💩');
        $this->assertSame('💩', $a->localName);
        $d->destroy();
    }

    public function testMethod_createAttributeNS(): void {
        // Local names are NOT lowercased in HTML documents
        $d = new Document();
        $a = $d->createAttributeNS('https://💩.com', '💩:POO');
        $this->assertSame('POO', $a->localName);
        $this->assertSame('https://💩.com', $a->namespaceURI);
        $this->assertSame('💩', $a->prefix);

        // Empty string namespaces and empty prefixes are supposed to be null
        $a = $d->createAttributeNS('', '💩');
        $this->assertSame('💩', $a->localName);
        $this->assertSame(null, $a->namespaceURI);
        $this->assertSame(null, $a->prefix);
        $d->destroy();
    }

    public function testMethod_createCDATASection(): void {
        $d = new XMLDocument();
        $c = $d->createCDATASection('💩poo');
        $this->assertSame('💩poo', $c->data);
        $this->assertInstanceOf(CDataSection::class, $c);
        $d->destroy();
    }

    public function testMethod_createComment(): void {
        $d = new Document();
        $c = $d->createComment('💩poo');
        $this->assertSame('💩poo', $c->data);
        $this->assertInstanceOf(Comment::class, $c);
        $d->destroy();
    }

    public function testMethod_createDocumentFragment(): void {
        $d = new Document();
        $f = $d->createDocumentFragment();
        $t = $d->createTextNode('ook');
        $f->appendChild($t);
        $this->assertSame($t, $f->firstChild);
        $this->assertInstanceOf(DocumentFragment::class, $f);
        $d->destroy();
    }

    public function testMethod_createElement(): void {
        $d = new Document();
        $c = $d->createElement('poo💩');
        $this->assertSame('poo💩', $c->localName);
        $this->assertInstanceOf(Element::class, $c);
        $d->destroy();
    }

    public function testMethod_createElementNS(): void {
        // Local names ARE lowercased in HTML documents when namespaced
        $d = new Document();
        $a = $d->createElementNS('https://💩.com', '💩:POO');
        $this->assertSame('POO', $a->localName);
        $this->assertSame('https://💩.com', $a->namespaceURI);
        $this->assertSame('💩', $a->prefix);

        // Empty string namespaces and empty prefixes are supposed to be null even in
        // HTML documents
        $a = $d->createElementNS('', '💩');
        $this->assertSame('💩', $a->localName);
        $this->assertSame(null, $a->namespaceURI);
        $this->assertSame(null, $a->prefix);
        $d->destroy();
    }

    public function testMethod_createProcessingInstruction(): void {
        $d = new Document();
        $c = $d->createProcessingInstruction('💩', '💩poo');
        $this->assertSame('💩', $c->target);
        $this->assertSame('💩poo', $c->data);
        $this->assertInstanceOf(ProcessingInstruction::class, $c);
        $d->destroy();
    }

    public function testMethod_evaluate(): void {
        $d = new Document('<!DOCTYPE html><html><body><poo💩>ook</poo💩><poo💩>eek</poo💩><div id="poo"><poo💩>ack</poo💩></div></body></html>');
        // NodeList return value (XPath is NOT coerced)
        $r = $d->evaluate('//pooU01F4A9//text()');
        $this->assertEquals(3, count($r));
        // Number return value (XPath is NOT coerced)
        $r = $d->evaluate('count(//pooU01F4A9//text())');
        $this->assertEquals(3, $r);
        // String return value (XPath is NOT coerced)
        $r = $d->evaluate('name(//pooU01F4A9)');
        $this->assertSame('poo💩', $r);
        // Context node - NodeList return value (XPath is NOT coerced)
        $r = $d->evaluate('./pooU01F4A9//text()', $d->getElementById('poo'));
        $this->assertEquals(1, count($r));
        $d->destroy();
    }

    public function testMethod_getElementsByName(): void {
        $d = new Document('<!DOCTYPE html><html><body><div name="poo💩">ook</div><div name="poo💩">eek</div><div name="poo💩">ack</div></body></html>');
        $l = $d->getElementsByName('poo💩');
        $this->assertEquals(3, $l->length);
        $this->assertEquals('<div name="poo💩">ook</div>', (string)$l[0]);
        $d->destroy();
    }

    public function testMethod_loadFile(): void {
        // Test with a local file
        $d = new Document();
        $d->loadFile(__DIR__ . '/../misc/test.html');
        $this->assertSame('ISO-2022-JP', $d->charset);
        $this->assertStringEndsWith('tests/misc/test.html', $d->URL);
        $d->destroy();

        // Test with a virtual stream
        $d = new Document();
        $v = vfsStream::setup('ook', 0777, [
            '1.html' => <<<HTML
            <!DOCTYPE html>
            <html>
             <head>
              <meta charset="ISO-2022-JP">
              <title>Ook</title>
             </head>
            </html>
            HTML
        ]);

        $d->loadFile($v->url() . '/1.html');
        $this->assertSame('ISO-2022-JP', $d->charset);
        $this->assertStringStartsWith('vfs://', $d->URL);
        $d->destroy();

        // Test with an http stream
        $d = new Document();
        $d->loadFile('https://google.com');
        $this->assertSame('https://google.com', $d->documentURI);
        $d->destroy();
    }

    public function testMethod_offsetExists(): void {
        $d = new Document('<!DOCTYPE html><html><body><img name="poo💩"></body></html>');
        $this->assertTrue(isset($d['poo💩']));
        $d->destroy();
    }

    public function testMethod_offsetGet(): void {
        $d = new Document('<!DOCTYPE html><html><body><img id="ook" name="poo💩"><iframe name="poo💩"></iframe><form name="poo💩"></form></body></html>');
        // Returning an HTMLCollection
        $this->assertInstanceOf(HTMLCollection::class, $d['poo💩']);
        $this->assertEquals(3, $d['poo💩']->length);
        // Returning one element
        $this->assertInstanceOf(Element::class, $d['ook']);
        $this->assertSame('IMG', $d['ook']->nodeName);
        // Returning null
        $this->assertNull($d['eek']);
        $d->destroy();
    }

    public function testMethod_registerXPathFunctions(): void {
        $d = new Document('<!DOCTYPE html><html><body><h1>Ook</h1><p class="poo💩">ook</p><p class="poo💩poo💩">eek</p></body></html>');
        $d->registerXPathFunctions();
        $r = $d->evaluate('//*[php:functionString("substr", @class, 0, 7) = "poo💩"]', $d);
        $this->assertEquals(2, $r->length);
        $d->destroy();
    }

    public function testMethod_register_unregisterXPathNamespaces(): void {
        $d = new Document('<!DOCTYPE html><html><body><svg xmlns="http://www.w3.org/2000/svg"><text x="20" y="0">Ook</text></svg></body></html>');
        $d->registerXPathNamespaces([ 'svg' => Node::SVG_NAMESPACE ]);
        $r = $d->evaluate('//svg:svg/svg:text/text()', $d);
        $this->assertSame('Ook', $r[0]->textContent);
        $d->unregisterXPathNamespaces('svg');
        $r = $d->evaluate('//svg:svg/svg:text/text()', $d);
        $this->assertEquals(0, $r->length);
        $d->destroy();
    }

    public function testMethod_serializeInner(): void {
        $d = new Document('<!DOCTYPE html><html><body><svg xmlns="http://www.w3.org/2000/svg"><text x="20" y="0">Ook</text></svg></body></html>');
        $this->assertSame('<svg xmlns="http://www.w3.org/2000/svg"><text x="20" y="0">Ook</text></svg>', $d->serializeInner($d->body));
        $d->destroy();
    }

    public function testMethod_toString(): void {
        $d = new Document('<!DOCTYPE html><html><body><svg xmlns="http://www.w3.org/2000/svg"><text x="20" y="0">Ook</text></svg></body></html>');
        $this->assertSame('<!DOCTYPE html><html><head></head><body><svg xmlns="http://www.w3.org/2000/svg"><text x="20" y="0">Ook</text></svg></body></html>', (string)$d);
        $d->destroy();
    }


    public function testProperty_body(): void {
        $d = new Document();
        $this->assertNull($d->body);
        $d->appendChild($d->createElement('html'));
        $this->assertNull($d->body);
        $d->documentElement->appendChild($d->createElement('body'));
        $this->assertNotNull($d->body);
        $d->destroy();
    }

    public function testProperty_contentType(): void {
        $d = new Document();
        $this->assertSame('text/html', $d->contentType);
        $d->destroy();
        $d = new XMLDocument();
        $this->assertSame('application/xml', $d->contentType);
        $d->destroy();
    }

    public function testProperty_designMode(): void {
        $d = new Document();
        $this->assertSame('off', $d->designMode);
        $d->designMode = 'on';
        $this->assertSame('on', $d->designMode);
        $d->designMode = 'off';
        $this->assertSame('off', $d->designMode);
        $d->destroy();
    }

    public function testProperty_dir(): void {
        $d = new Document('<!DOCTYPE html><html dir="ltr"></html>');
        $this->assertSame('ltr', $d->dir);
        $d->dir = 'bullshit';
        $this->assertSame('', $d->dir);
        $d->destroy();
    }

    public function testProperty_doctype() {
        $d = new Document();
        $this->assertNull($d->doctype);

        $doctype = $d->appendChild($d->implementation->createDocumentType('html', '', ''));
        $this->assertSame($doctype, $d->doctype);
        $d->destroy();
    }

    public function testProperty_embeds() {
        $d = new Document('<!DOCTYPE html><html><body><embed></embed><embed></embed><embed></embed><div><div><div><embed></embed></div></div></div></body></html>');
        $this->assertEquals(4, $d->embeds->length);
        $this->assertEquals(4, $d->plugins->length);
        $d->destroy();
    }

    public function testProperty_forms() {
        $d = new Document('<!DOCTYPE html><html><body><form></form><form></form><form></form><div><div><div><form></form></div></div></div><template><form></form></template></body></html>');
        $this->assertEquals(4, $d->forms->length);
        $d->destroy();
    }

    public function testProperty_head() {
        $d = new Document();
        $this->assertNull($d->head);

        $de = $d->appendChild($d->createElement('html'));
        $head = $de->appendChild($d->createElement('head'));

        $this->assertSame($head, $d->head);
        $d->destroy();
    }

    public function testProperty_images() {
        $d = new Document('<!DOCTYPE html><html><body><img><img><img><div><div><div><img></div></div></div><template><img></template></body></html>');
        $this->assertEquals(4, $d->images->length);
        $d->destroy();
    }

    public function testProperty_links() {
        $d = new Document('<!DOCTYPE html><html><body><a href=""></a><a href=""></a><a href=""></a><div><div><div><area href=""></area></div></div></div><template><a href=""></a></template></body></html>');
        $this->assertEquals(4, $d->links->length);
        $d->destroy();
    }

    public function testProperty_scripts() {
        $d = new Document('<!DOCTYPE html><html><body><script></script><script></script><script></script><div><div><div><script></script></div></div></div><template><script></script></template></body></html>');
        $this->assertEquals(4, $d->scripts->length);
        $d->destroy();
    }

    public function testProperty_title() {
        $d = new Document();
        $this->assertSame('', $d->title);
        $d->title = 'fail';
        $this->assertSame('', $d->title);
        $d->destroy();

        $d = (new DOMImplementation)->createDocument(Node::SVG_NAMESPACE, 'svg');
        $this->assertSame('', $d->title);

        $d->title = 'Ook';
        $this->assertSame('Ook', $d->title);
        $d->title = '   Ee  k  ';
        $this->assertSame('Ee k', $d->title);
        $d->destroy();

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


    #[DataProvider('provideFatalErrors')]
    public function testFatalErrors(string $throwableClassName, \Closure $closure): void {
        $this->expectException($throwableClassName);
        $d = new Document();
        $closure($d);
        $d->destroy();
    }

    public static function provideFatalErrors(): iterable {
        $iterable = [
            // Attempting to adopt a Document
            [
                NotSupportedError::class,
                function (Document $d): void {
                    $d->adoptNode($d);
                }
            ],
            // Invalid attribute name
            [
                InvalidCharacterError::class,
                function (Document $d): void {
                    $d->createAttribute(' ');
                }
            ],
            // Loading into a non-empty document
            [
                NoModificationAllowedError::class,
                function (Document $d): void {
                    $d->load('<!DOCTYPE html><html></html>');
                    $d->load('fail');
                }
            ],
            // Importing a Document
            [
                NotSupportedError::class,
                function (Document $d): void {
                    $d->importNode(new Document());
                }
            ],
            // Importing a \DOMElement without an owner document
            [
                NotSupportedError::class,
                function (Document $d): void {
                    $d->importNode(new \DOMElement('fail'));
                }
            ],
            // Importing a \DOMNode with a non-\DOMDocument owner
            [
                NotSupportedError::class,
                function (Document $d): void {
                    $d2 = new class extends \DOMDocument {};
                    $d->importNode($d2->createTextNode('fail'));
                }
            ],
            // Importing a \DOMEntityReference
            [
                NotSupportedError::class,
                function (Document $d): void {
                    $d2 = new \DOMDocument();
                    $d->importNode($d2->createEntityReference('nbsp'));
                }
            ],
            // Creating a CDataSection on an HTML document
            [
                NotSupportedError::class,
                function (Document $d): void {
                    $d->createCDATASection('fail');
                }
            ],
            // Creating a CDataSection with ']]>' in the data
            [
                InvalidCharacterError::class,
                function (Document $d): void {
                    $d = new XMLDocument();
                    $d->createCDATASection(']]>');
                    $d->destroy();
                }
            ],
            // Invalid element name
            [
                InvalidCharacterError::class,
                function (Document $d): void {
                    $d->createElement(' ');
                }
            ],
            // Invalid HTML element name
            [
                InvalidCharacterError::class,
                function (Document $d): void {
                    $d->createElement('💩poo');
                }
            ],
            // Invalid XPath expression
            [
                XPathException::class,
                function (Document $d): void {
                    $d->evaluate(' ');
                }
            ],
            // Undefined namespace prefix
            [
                XPathException::class,
                function (Document $d): void {
                    $d->evaluate('//svg:svg');
                }
            ],
            // Loading file when Document isn't empty
            [
                NoModificationAllowedError::class,
                function (Document $d): void {
                    $d->appendChild($d->createElement('html'));
                    $d->loadFile('fail.html');
                }
            ],
            // File not found when loading
            [
                FileNotFoundException::class,
                function (Document $d): void {
                    $d->loadFile('fail.html');
                }
            ],
            // Isset on an invalid named property
            [
                InvalidArgumentException::class,
                function (Document $d): void {
                    isset($d[0]);
                }
            ],
            // Getting an invalid named property
            [
                InvalidArgumentException::class,
                function (Document $d): void {
                    $d[0];
                }
            ],
            // Invalid prefix array
            [
                InvalidArgumentException::class,
                function (Document $d): void {
                    $d->registerXPathNamespaces([ 42, 'fail' ]);
                }
            ],
            // Invalid prefix array
            [
                InvalidArgumentException::class,
                function (Document $d): void {
                    $d->registerXPathNamespaces([ 'fail', 42 ]);
                }
            ],
            // Serializing wrong document
            [
                WrongDocumentError::class,
                function (Document $d): void {
                    $d2 = new Document();
                    $e = $d2->createElement('fail');
                    $d->serialize($e);
                }
            ],
            // Serializing wrong document
            [
                WrongDocumentError::class,
                function (Document $d): void {
                    $d2 = new Document();
                    $e = $d2->createElement('fail');
                    $d->serializeInner($e);
                }
            ]
        ];

        foreach ($iterable as $i) {
            yield $i;
        }
    }
}