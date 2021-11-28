<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\Inner\{
    Document as InnerDocument,
    Reflection
};


class DOMImplementation {
    protected \WeakReference $document;
    protected static array $documentsCache = [];


    public function __construct(?Document $document = null) {
        if ($document !== null) {
            $this->document = \WeakReference::create($document);
        } else {
            // Under most uses of this class the document is a weak reference to a stored
            // Document object. When creating an implementation without an existing Document
            // this class will create a weak reference to a newly-instantiated Document
            // class. This presents a problem because that new Document is immediately
            // garbage collected because there's no longer a reference pointing to it,
            // making the weak reference return null instead of the Document it should be
            // returning. To circumvent this we're going to have a static documents cache
            // that's used to keep a reference around.
            $this->document = \WeakReference::create(self::$documentsCache[] = new Document());
        }
    }


    public function createDocument(?string $namespace = null, string $qualifiedName, ?DocumentType $doctype = null): XMLDocument {
        # The createDocument(namespace, qualifiedName, doctype) method steps are:
        #
        # 1. Let document be a new XMLDocument.
        $document = Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\XMLDocument');

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
            case Node::HTML_NAMESPACE:
                # application/xhtml+xml
                $contentType = 'application/xhtml+xml';
            break;
            # ↪ SVG namespace
            case Node::SVG_NAMESPACE:
                # image/svg+xml
                $contentType = 'image/svg+xml';
            break;
            # ↪ Any other namespace
            default:
                # application/xml
                $contentType = 'application/xml';
        }

        Reflection::setProtectedProperties($document, ['_contentType' => $contentType ]);

        # 8. Return document.
        return $document;
    }

    public function createDocumentType(string $qualifiedName, string $publicId, string $systemId): DocumentType {
        # The createDocumentType(qualifiedName, publicId, systemId) method steps are:
        #
        # 1. Validate qualifiedName.
        #    To validate a qualifiedName, throw an "InvalidCharacterError" DOMException if
        #    qualifiedName does not match the QName production.
        if (!preg_match(InnerDocument::QNAME_PRODUCTION_REGEX, $qualifiedName)) {
            throw new DOMException(DOMException::INVALID_CHARACTER);
        }

        # 2. Return a new doctype, with qualifiedName as its name, publicId as its
        #    public ID, and systemId as its system ID, and with its node document set to
        #    the associated document of this.
        $innerDocument = Reflection::getProtectedProperty($this->document->get(), 'innerNode');
        // PHP's DOM won't accept an empty string as the qualifiedName, so use a space
        // instead which won't be encountered elsewhere because it violates the QName
        // production; this will be worked around in DocumentType.
        return $innerDocument->getWrapperNode($innerDocument->implementation->createDocumentType(($qualifiedName !== '') ? $qualifiedName : ' ', $publicId, $systemId));
    }

    public function createHTMLDocument(string $title = ''): Document {
        # The createHTMLDocument(title) method steps are:
        #
        # 1. Let doc be a new document that is an HTML document.
        $doc = Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\Document');

        # 2. Set doc’s content type to "text/html".
        // Already done because of how this library is to be used.

        # 3. Append a new doctype, with "html" as its name and with its node document
        #    set to doc, to doc.
        $doc->appendChild($doc->implementation->createDocumentType('html', '', ''));

        # 4. Append the result of creating an element given doc, html, and the HTML
        #    namespace, to doc.
        $documentElement = $doc->appendChild($doc->createElement('html'));

        # 5. Append the result of creating an element given doc, head, and the HTML
        #    namespace, to the html element created earlier.
        $head = $documentElement->appendChild($doc->createElement('head'));

        # 6. If title is given:
        if ($title !== '') {
            # 1. Append the result of creating an element given doc, title, and the HTML
            #    namespace, to the head element created earlier.
            $t = $head->appendChild($doc->createElement('title'));

            # 2. Append a new Text node, with its data set to title (which could be the empty
            #    string) and its node document set to doc, to the title element created earlier.
            $t->appendChild($doc->createTextNode($title));
        }

        # 7. Append the result of creating an element given doc, body, and the HTML
        #    namespace, to the html element created earlier.
        $documentElement->appendChild($doc->createElement('body'));

        # 8. doc’s origin is this’s associated document’s origin.
        // Not necessary. No scripting in this implementation.

        # 9. Return doc.
        return $doc;
    }

    public function hasFeature(): bool {
        return true;
    }
}