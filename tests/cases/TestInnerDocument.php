<?php
/**
 * @license MIT
 * Copyright 2022 Dustin Wilson, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\Test;
use MensBeam\HTML\DOM\{
    Document,
    HTMLElement,
    HTMLPreElement,
    HTMLTemplateElement,
    HTMLUnknownElement,
    MathMLElement,
    Node,
    SVGElement
};
use MensBeam\HTML\DOM\DOMException\WrongDocumentError;
use PHPUnit\Framework\{
    TestCase,
    Attributes\CoversClass,
    Attributes\DataProvider
};
// use org\bovigo\vfs\vfsStream;


#[CoversClass('MensBeam\HTML\DOM\Inner\Document')]
#[CoversClass('MensBeam\HTML\DOM\Document')]
#[CoversClass('MensBeam\HTML\DOM\HTMLElement')]
#[CoversClass('MensBeam\HTML\DOM\HTMLPreElement')]
#[CoversClass('MensBeam\HTML\DOM\HTMLTemplateElement')]
#[CoversClass('MensBeam\HTML\DOM\HTMLUnknownElement')]
#[CoversClass('MensBeam\HTML\DOM\MathMLElement')]
#[CoversClass('MensBeam\HTML\DOM\Node')]
#[CoversClass('MensBeam\HTML\DOM\SVGElement')]
#[CoversClass('MensBeam\HTML\DOM\DOMException\WrongDocumentError')]
class TestInnerDocument extends TestCase {
    public function testMethod_getWrapperNode(): void {
        // Everything tests this method thoroughly except some element interfaces.
        $d = new Document();
        $this->assertSame(HTMLUnknownElement::class, $d->createElement('applet')::class);
        $this->assertSame(HTMLElement::class, $d->createElement('noembed')::class);
        $this->assertSame(HTMLPreElement::class, $d->createElement('xmp')::class);
        $this->assertSame(HTMLPreElement::class, $d->createElement('pre')::class);
        $this->assertSame(MathMLElement::class, $d->createElementNS(Node::MATHML_NAMESPACE, 'math')::class);
        $this->assertSame(HTMLTemplateElement::class, $d->createElement('template')::class);
        $this->assertSame(HTMLElement::class, $d->createElement('p-icon')::class);
        $this->assertSame(SVGElement::class, $d->createElementNS(Node::SVG_NAMESPACE, 'svg')::class);
    }


    #[DataProvider('provideFatalErrors')]
    public function testFatalErrors(string $throwableClassName, \Closure $closure): void {
        $this->expectException($throwableClassName);
        $closure();
    }

    public static function provideFatalErrors(): iterable {
        $iterable = [
            // Attempting to get the wrapper node of another document
            [
                WrongDocumentError::class,
                function (): void {
                    $d = new Document();
                    $d->innerNode->getWrapperNode(new \DOMDocument());
                }
            ],
            // Attempting to get the wrapper node of another document's node
            [
                WrongDocumentError::class,
                function (): void {
                    $d = new Document();
                    $d2 = new Document();
                    $d->innerNode->getWrapperNode($d2->innerNode->createTextNode('fail'));
                }
            ]
        ];

        foreach ($iterable as $i) {
            yield $i;
        }
    }
}