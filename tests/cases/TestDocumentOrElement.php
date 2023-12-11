<?php
/**
 * @license MIT
 * Copyright 2022 Dustin Wilson, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\Test;
use MensBeam\HTML\DOM\Document;
use MensBeam\HTML\DOM\DOMException\{
    InvalidCharacterError,
    NamespaceError
};
use PHPUnit\Framework\{
    TestCase,
    Attributes\CoversClass,
    Attributes\DataProvider
};


#[CoversClass('MensBeam\HTML\DOM\DocumentOrElement')]
#[CoversClass('MensBeam\HTML\DOM\Document')]
#[CoversClass('MensBeam\HTML\DOM\DOMException\InvalidCharacterError')]
#[CoversClass('MensBeam\HTML\DOM\DOMException\NamespaceError')]
class TestDocumentOrElement extends TestCase {
    #[DataProvider('provideFatalErrors')]
    public function testFatalErrors(string $throwableClassName, \Closure $closure): void {
        $this->expectException($throwableClassName);
        $d = new Document();
        $closure($d);
        $d->destroy();
    }

    public static function provideFatalErrors(): iterable {
        $iterable = [
            // Invalid attribute name
            [
                InvalidCharacterError::class,
                function (Document $d): void {
                    $d->createAttributeNS('fail', ' ');
                }
            ],
            // Attribute: non-null prefix, null/empty namespace
            [
                NamespaceError::class,
                function (Document $d): void {
                    $d->createAttributeNS('', 'fail:fail');
                }
            ],
            // Attribute: prefix is 'xml', namespace is not xml namespace
            [
                NamespaceError::class,
                function (Document $d): void {
                    $d->createAttributeNS('fail', 'xml:fail');
                }
            ],
            // Attribute: qualified name is 'xmlns', namespace is not xmlns namespace
            [
                NamespaceError::class,
                function (Document $d): void {
                    $d->createAttributeNS('fail', 'xmlns');
                }
            ],
            // Attribute: prefix is 'xmlns', namespace is not xmlns namespace
            [
                NamespaceError::class,
                function (Document $d): void {
                    $d->createAttributeNS('fail', 'xmlns:fail');
                }
            ]
        ];

        foreach ($iterable as $i) {
            yield $i;
        }
    }
}