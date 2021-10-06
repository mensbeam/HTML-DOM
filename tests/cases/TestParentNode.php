<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
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


/** @covers \MensBeam\HTML\DOM\ParentNode */
class TestParentNode extends \PHPUnit\Framework\TestCase {
    public function providePreInsertionValidationFailures(): iterable {
        return [
            [ function() {
                $d = new Document();
                $d->appendChild($d->createElement('html'));
                $b = $d->documentElement->appendChild($d->createElement('body'));
                $b->appendChild($d->documentElement);
            }, DOMException::HIERARCHY_REQUEST_ERROR ],
            [ function() {
                $d = new Document();
                $t = $d->appendChild($d->createElement('template'));
                $d->appendChild($t->content);
            }, DOMException::HIERARCHY_REQUEST_ERROR ],
            [ function() {
                $d = new Document();
                $d->appendChild($d->createElement('html'));
                $b = $d->documentElement->appendChild($d->createElement('body'));
                $t = $b->appendChild($d->createElement('template'));
                $t->content->appendChild($b);
            }, DOMException::HIERARCHY_REQUEST_ERROR ],
            [ function() {
                $d = new Document();
                $d->appendChild($d->createElement('html'));
                $b = $d->documentElement->appendChild($d->createElement('body'));
                $d->insertBefore($d->createElement('fail'), $b);
            }, DOMException::NOT_FOUND ],
            [ function() {
                $d = new Document();
                $df = $d->createDocumentFragment();
                $df->appendChild($d->createElement('html'));
                $df->appendChild($d->createTextNode(' '));
                $d->appendChild($df);
            }, DOMException::HIERARCHY_REQUEST_ERROR ],
            [ function() {
                $d = new Document();
                $d->appendChild($d);
            }, DOMException::HIERARCHY_REQUEST_ERROR ],
            [ function() {
                $d = new Document();
                $d->appendChild($d->implementation->createDocumentType('html'));
                $d->appendChild($d->implementation->createDocumentType('html'));
            }, DOMException::HIERARCHY_REQUEST_ERROR ],
            [ function() {
                $d = new Document();
                $d->appendChild($d->createElement('html'));
                $d->appendChild($d->implementation->createDocumentType('html'));
            }, DOMException::HIERARCHY_REQUEST_ERROR ],
            [ function() {
                $d = new Document();
                $d->appendChild($d->createElement('html'));
                $c = $d->appendChild($d->createComment('ook'));
                $d->insertBefore($d->implementation->createDocumentType('html'), $c);
            }, DOMException::HIERARCHY_REQUEST_ERROR ],
            [ function() {
                $d = new Document();
                $d->appendChild($d->createElement('html'));
                $d->documentElement->insertBefore($d->implementation->createDocumentType('html'));
            }, DOMException::HIERARCHY_REQUEST_ERROR ],
            /*[ function() {
                $d = new Document();
                $d->createElementNS(null, '<ook>');
            }, DOMException::INVALID_CHARACTER ],
            [ function() {
                $d = new Document();
                $d->createElementNS(null, 'xmlns');
            }, DOMException::NAMESPACE_ERROR ]*/
        ];
    }

    /**
     * @dataProvider providePreInsertionValidationFailures
     * @covers \MensBeam\HTML\DOM\Document::createElementNS
     * @covers \MensBeam\HTML\DOM\Document::validateAndExtract
     */
    public function testPreInsertionValidationFailures(\Closure $closure, int $errorCode): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode($errorCode);
        $closure();
    }
}