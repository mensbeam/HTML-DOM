<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\InnerNode\Factory,
    MensBeam\HTML\DOM\Parser;


class DOMImplementation {
    protected \WeakReference $document;


    protected function __construct(Document $document) {
        $this->document = \WeakReference::create($document);
    }


    public function createDocument(?string $namespace = null, string $qualifiedName, ?DocumentType $doctype = null): XMLDocument {
        # The createDocument(namespace, qualifiedName, doctype) method steps are:
        #
        # 1. Let document be a new XMLDocument.
        $document = Factory::createFromProtectedConstructor(__NAMESPACE__ . '\\XMLDocument');

        # 2. Let element be null.
        $element = null;

        # 3. If qualifiedName is not the empty string, then set element to the result of
        #    running the internal createElementNS steps, given document, namespace,
        #    qualifiedName, and an empty dictionary.
        if ($qualifiedName !== '') {
            $element = $document->createElementNS($namespace, $qualifiedName);
        }

        # 4. If doctype is non-null, append doctype to document.
        if ($doctype !== null) {
            $document->appendChild($doctype);
        }

        # 5. If element is non-null, append element to document.
        if ($element !== null) {
            $document->appendChild($element);
        }

        # 6. document’s origin is this’s associated document’s origin.
        // DEVIATION: There is no scripting in this implementation.

        # 7. document’s content type is determined by namespace:
        switch ($namespace) {
            # ↪ HTML namespace
            case Parser::HTML_NAMESPACE:
                # application/xhtml+xml
                $contentType = 'application/xhtml+xml';
            break;
            # ↪ SVG namespace
            case Parser::SVG_NAMESPACE:
                # image/svg+xml
                $contentType = 'image/svg+xml';
            break;
            # ↪ Any other namespace
            default:
                # application/xml
                $contentType = 'application/xml';
        }

        Factory::setProtectedProperty($document, '_contentType', $contentType);

        # 8. Return document.
        return $document;
    }

    public function createDocumentType(string $qualifiedName, string $publicId, string $systemId): DocumentType {
        # The createDocumentType(qualifiedName, publicId, systemId) method steps are:
        #
        # 1. Validate qualifiedName.
        #    To validate a qualifiedName, throw an "InvalidCharacterError" DOMException if
        #    qualifiedName does not match the QName production.
        if (!preg_match('/^([A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}][A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}-\.0-9\x{B7}\x{0300}-\x{036F}\x{203F}-\x{2040}]*:)?[A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}][A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}-\.0-9\x{B7}\x{0300}-\x{036F}\x{203F}-\x{2040}]*$/Su', $qualifiedName)) {
            throw new DOMException(DOMException::INVALID_CHARACTER);
        }

        # 2. Return a new doctype, with qualifiedName as its name, publicId as its
        #    public ID, and systemId as its system ID, and with its node document set to
        #    the associated document of this.
        $innerDocument = Factory::getProtectedProperty($this->document->get(), 'innerNode');
        // PHP's DOM won't accept an empty string as the qualifiedName, so use a space
        // instead; this will be worked around in DocumentType.
        return $innerDocument->getWrapperNode($innerDocument->implementation->createDocumentType(($qualifiedName !== '') ? $qualifiedName : ' ', $publicId, $systemId));
    }

    public function createHTMLDocument(string $title = ''): Document {
        $document = Factory::createFromProtectedConstructor(__NAMESPACE__ . '\\Document');
        if ($title !== '') {
            $document->title = $title;
        }

        return $document;
    }

    public function hasFeature(): bool {
        return true;
    }
}