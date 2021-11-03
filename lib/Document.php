<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\InnerNode\{
    Document as InnerDocument,
    Reflection
};
use MensBeam\HTML\Parser;
use MensBeam\HTML\Parser\{
    Charset,
    Data,
    Config as ParserConfig,
    Serializer
};


class Document extends Node {
    use DocumentOrElement, NonElementParentNode, ParentNode;

    protected string $_characterSet = 'UTF-8';
    protected string $_compatMode = 'CSS1Compat';
    protected string $_contentType = 'text/html';
    protected DOMImplementation $_implementation;
    protected string $_URL = 'about:blank';

    protected function __get_body(): ?Element {
        $documentElement = $this->innerNode->documentElement;
        if ($documentElement === null) {
            return null;
        }

        # The body element of a document is the first of the html element's children
        # that is either a body element or a frameset element, or null if there is no
        # such element.
        $n = $documentElement->firstChild;
        if ($n !== null) {
            do {
                if ($n instanceof \DOMElement && $n->namespaceURI === null && ($n->nodeName === 'body' || $n->nodeName === 'frameset')) {
                    return $n->ownerDocument->getWrapperNode($n);
                }
            } while ($n = $n->nextSibling);
        }

        return null;
    }

    protected function __get_charset(): string {
        return $this->_characterSet;
    }

    protected function __get_characterSet(): string {
        return $this->_characterSet;
    }

    protected function __get_compatMode(): string {
        return $this->_compatMode;
    }

    protected function __get_contentType(): string {
        return $this->_contentType;
    }

    protected function __get_doctype(): ?DocumentType {
        // PHP's DOM does this correctly already.
        $doctype = $this->innerNode->doctype;
        return ($doctype !== null) ? $this->innerNode->getWrapperNode($doctype) : null;
    }

    protected function __get_documentElement(): ?Element {
        // PHP's DOM does this correctly already.
        $documentElement = $this->innerNode->documentElement;
        return ($documentElement !== null) ? $this->innerNode->getWrapperNode($documentElement) : null;
    }

    protected function __get_documentURI(): string {
        return $this->_URL;
    }

    protected function __get_implementation(): DOMImplementation {
        return $this->_implementation;
    }

    protected function __get_inputEncoding(): string {
        return $this->_characterSet;
    }

    protected function __get_URL(): string {
        return $this->_URL;
    }


    public function __construct(string $source = null, ?string $charset = null) {
        parent::__construct(new InnerDocument($this));
        $this->_implementation = new DOMImplementation($this);

        if ($source !== null) {
            $this->load($source, $charset);
        } elseif ($charset !== 'UTF-8') {
            $this->_characterSet = Charset::fromCharset((string)$charset) ?? 'UTF-8';
        }
    }


    public function adoptNode(Node &$node): Node {
        # The adoptNode(node) method steps are:
        #
        # 1. If node is a document, then throw a "NotSupportedError" DOMException.
        if ($node instanceof Document) {
            throw new DOMException(DOMException::NOT_SUPPORTED);
        }

        # 2. If node is a shadow root, then throw a "HierarchyRequestError" DOMException.
        // DEVIATION: There is no scripting in this implementation

        # 3. If node is a DocumentFragment node whose host is non-null, then return.
        // DEVIATION: One can't just return here?
        if ($node instanceof DocumentFragment) {
            $host = Reflection::getProtectedProperty($node, 'host');
            if ($host === null || $host->get() === null) {
                return $node;
            }
        }

        # 4. Adopt node into this.
        $newNode = $this->importNode($node, true);

        $parent = $node->parentNode;
        if ($parent !== null) {
            $parent->removeChild($node);
        }

        // Remove node from the inner document's node cache.
        Reflection::getProtectedProperty($this->getInnerNode($node)->ownerDocument, 'nodeCache')->delete($node);

        # 5. Return node.
        $node = $newNode;
        return $node;
    }

    public function createAttribute(string $localName): Attr {
        # The createAttribute(localName) method steps are:
        #
        # 1. If localName does not match the Name production in XML, then throw an
        #    "InvalidCharacterError" DOMException.
        if (preg_match(InnerDocument::NAME_PRODUCTION_REGEX, $localName) !== 1) {
            throw new DOMException(DOMException::INVALID_CHARACTER);
        }

        # 2. If this is an HTML document, then set localName to localName in ASCII
        #    lowercase.
        if (!$this instanceof XMLDocument) {
            $localName = strtolower($localName);
        }

        return $this->__createAttribute(null, $localName);
    }

    public function createAttributeNS(?string $namespace, string $qualifiedName): Attr {
        # The createAttributeNS(namespace, qualifiedName) method steps are:
        #
        # 1. Let namespace, prefix, and localName be the result of passing namespace and
        #    qualifiedName to validate and extract.
        [ 'namespace' => $namespace, 'prefix' => $prefix, 'localName' => $localName ] = $this->validateAndExtract($qualifiedName, $namespace);
        $qualifiedName = ($prefix !== null) ? "$prefix:$localName" : $localName;

        // Before we do the next step we need to work around a PHP DOM bug. PHP DOM
        // cannot create attribute nodes if there's no document element. So, create the
        // attribute node in a separate document which does have a document element and
        // then import
        $target = $this->innerNode;
        $documentElement = $this->documentElement;
        if ($documentElement === null) {
            $target = new \DOMDocument();
            $target->appendChild($target->createElement('html'));
        }

        # 2. Return a new attribute whose namespace is namespace, namespace prefix is
        #    prefix, local name is localName, and node document is this.
        // We need to do a couple more things here. PHP's XML-based DOM doesn't allow
        // some characters. We have to coerce them sometimes.
        try {
            $attr = $target->createAttributeNS($namespace, $qualifiedName);
        } catch (\DOMException $e) {
            // The element name is invalid for XML
            // Replace any offending characters with "UHHHHHH" where H are the
            //   uppercase hexadecimal digits of the character's code point
            if ($prefix !== null) {
                $qualifiedName = $this->coerceName($prefix) . ':' . $this->coerceName($localName);
            } else {
                $qualifiedName = $this->coerceName($localName);
            }

            $attr = $target->createAttributeNS($namespace, $qualifiedName);
        }

        if ($documentElement === null) {
            $attr = $this->cloneInnerNode($attr, $this->innerNode);
        }

        return $this->innerNode->getWrapperNode($attr);
    }

    public function createCDATASection(string $data): CDATASection {
        # The createCDATASection(data) method steps are:
        #
        # 1. If this is an HTML document, then throw a "NotSupportedError" DOMException.
        if (!$this instanceof XMLDocument) {
            throw new DOMException(DOMException::NOT_SUPPORTED);
        }

        # 2. If data contains the string "]]>", then throw an "InvalidCharacterError"
        #    DOMException.
        if (str_contains(needle: ']]>', haystack: $data)) {
            throw new DOMException(DOMException::INVALID_CHARACTER_ERROR);
        }

        # 3. Return a new CDATASection node with its data set to data and node document
        #    set to this.
        return $this->innerNode->getWrapperNode($this->innerNode->createCDATASection($data));
    }

    public function createComment(string $data): Comment {
        return $this->innerNode->getWrapperNode($this->innerNode->createComment($data));
    }

    public function createDocumentFragment(): DocumentFragment {
        return $this->innerNode->getWrapperNode($this->innerNode->createDocumentFragment());
    }

    public function createElement(string $localName): Element {
        # The createElement(localName, options) method steps are:
        // DEVIATION: The options parameter is at present only used for custom elements.
        // There is no scripting in this implementation.

        # 1. If localName does not match the Name production, then throw an
        #    "InvalidCharacterError" DOMException.
        if (!preg_match(InnerDocument::NAME_PRODUCTION_REGEX, $localName)) {
            throw new DOMException(DOMException::INVALID_CHARACTER);
        }

        # 2. If this is an HTML document, then set localName to localName in ASCII
        #    lowercase.
        if (!$this instanceof XMLElement) {
            $localName = strtolower($localName);
        }

        # 3. Let is be null.
        # 4. If options is a dictionary and options["is"] exists, then set is to it.
        // DEVIATION: There's no scripting in this implementation
        # 5. Let namespace be the HTML namespace, if this is an HTML document or this’s
        #    content type is "application/xhtml+xml"; otherwise null.
        // PHP's DOM has numerous bugs when setting the HTML namespace. Externally,
        // everything will show as HTML namespace, but internally will be null.
        # 6. Return the result of creating an element given this, localName, namespace,
        #    null, is, and with the synchronous custom elements flag set.

        try {
            $element = $this->innerNode->createElementNS(null, $localName);
        } catch (\DOMException $e) {
            // The element name is invalid for XML
            // Replace any offending characters with "UHHHHHH" where H are the
            // uppercase hexadecimal digits of the character's code point
            $element = $this->innerNode->createElementNS(null, $this->coerceName($localName));
        }

        return $this->innerNode->getWrapperNode($element);
    }

    public function createElementNS(string $namespace, string $qualifiedName): Element {
        # The internal createElementNS steps, given document, namespace, qualifiedName,
        # and options, are as follows:
        // DEVIATION: The options parameter is at present only used for custom elements.
        // There is no scripting in this implementation.

        # 1. Let namespace, prefix, and localName be the result of passing namespace and
        #    qualifiedName to validate and extract.
        [ 'namespace' => $namespace, 'prefix' => $prefix, 'localName' => $localName ] = $this->validateAndExtract($qualifiedName, $namespace);
        $qualifiedName = ($prefix) ? "$prefix:$localName" : $localName;

        # 2. Let is be null.
        # 3. If options is a dictionary and options["is"] exists, then set is to it.
        # 4. Return the result of creating an element given document, localName, namespace,
        #    prefix, is, and with the synchronous custom elements flag set.
        // DEVIATION: There is no scripting in this implementation.

        try {
            $element = $this->innerNode->createElementNS($namespace, $qualifiedName);
        } catch (\DOMException $e) {
            // The element name is invalid for XML
            // Replace any offending characters with "UHHHHHH" where H are the
            // uppercase hexadecimal digits of the character's code point
            if ($prefix !== null) {
                $qualifiedName = $this->coerceName($prefix) . ':' . $this->coerceName($localName);
            } else {
                $qualifiedName = $this->coerceName($qualifiedName);
            }

            $element = $this->innerNode->createElementNS($namespace, $qualifiedName);
        }

        return $this->innerNode->getWrapperNode($element);
    }

    public function createProcessingInstruction(string $target, string $data): ProcessingInstruction {
        try {
            $instruction = $this->innerNode->createProcessingInstruction($target, $data);
        } catch (\DOMException $e) {
            // The target is invalid for XML
            // Replace any offending characters with "UHHHHHH" where H are the
            // uppercase hexadecimal digits of the character's code point
            $instruction = $this->innerNode->createProcessingInstruction($this->coerceName($target), $data);
        }

        return $this->innerNode->getWrapperNode($instruction);
    }

    public function createTextNode(string $data): Text {
        return $this->innerNode->getWrapperNode($this->innerNode->createTextNode($data));
    }

    public function importNode(Node $node, bool $deep = false): Node {
        # The importNode(node, deep) method steps are:
        #
        # 1. If node is a document or shadow root, then throw a "NotSupportedError"
        #    DOMException.
        if ($node instanceof Document || $node instanceof \DOMDocument) {
            throw new DOMException(DOMException::NOT_SUPPORTED);
        }

        # 2. Return a clone of node, with this and the clone children flag set if deep
        #    is true.
        return $this->cloneWrapperNode($node, $this, $deep);
    }

    public function load(string $source = null, ?string $charset = null): void {
        if ($this->hasChildNodes()) {
            throw new DOMException(DOMException::NO_MODIFICATION_ALLOWED);
        }

        $config = null;
        if ($charset !== null) {
            $config = new ParserConfig();
            $config->encodingFallback = Charset::fromCharset($charset);
            // Preserve processing instructions when parsing. This violates the parsing
            // specification, but since this library is not a browser it should be able to
            // read and print processing instructions.
            $config->processingInstructions = true;
        }

        $source = Parser::parse($source, null, $config);
        $this->_characterSet = $source->encoding;
        $this->_compatMode = ($source->quirksMode === Parser::NO_QUIRKS_MODE || $source->$quirksMode === Parser::LIMITED_QUIRKS_MODE) ? 'CSS1Compat' : 'BackCompat';

        $source = $source->document;
        $childNodes = $source->childNodes;
        foreach ($source->childNodes as $child) {
            $this->innerNode->appendChild($this->cloneInnerNode($child, $this->innerNode, true));
        }
    }

    public function loadFile(string $filename, ?string $charset = null): void {
        $f = fopen($filename, 'r');
        if (!$f) {
            return;
        }

        $data = stream_get_contents($f);
        $charset = Charset::fromCharset((string)$charset) ?? Charset::fromTransport((string)$charset);
        $meta = stream_get_meta_data($f);
        $wrapperType = $meta['wrapper_type'];
        if (!$charset && $wrapperType === 'http') {
            // Try to find a Content-Type header field
            foreach ($meta['wrapper_data'] as $h) {
                $h = explode(':', $h, 2);
                if (count($h) === 2 && preg_match("/^\s*Content-Type\s*$/i", $h[0])) {
                    // Try to get an encoding from it
                    $charset = Charset::fromTransport($h[1]);
                    break;
                }
            }
        }

        if ($wrapperType === 'plainfile') {
            $filename = realpath($filename);
            $this->_URL = "file://$filename";
        } else {
            $this->_URL = $filename;
        }

        $this->load($data, $charset);
    }

    public function serialize(?Node $node = null): string {
        $node = $node ?? $this;
        if ($node !== $this) {
            if (!$this instanceof XMLDocument && $node instanceof CDATASection) {
                throw new DOMException(DOMException::NOT_SUPPORTED);
            }

            if ($node->ownerDocument !== $this) {
                throw new DOMException(DOMException::WRONG_DOCUMENT);
            }

            $node = $this->getInnerNode($node);
        } else {
            $node = $node->innerNode;
        }

        return Serializer::serialize($node);
    }


    protected function __createAttribute(?string $namespace, string $qualifiedName): Attr {
        // Before we do the next step we need to work around a PHP DOM bug. PHP DOM
        // cannot create attribute nodes if there's no document element. So, create the
        // attribute node in a separate document which does have a document element and
        // then import
        $target = $this->innerNode;
        $documentElement = $this->documentElement;
        if ($documentElement === null) {
            $target = new \DOMDocument();
            $target->appendChild($target->createElement('html'));
        }

        // From createAttributeNS:
        # 2. Return a new attribute whose namespace is namespace, namespace prefix is
        #    prefix, local name is localName, and node document is this.
        // We need to do a couple more things here. PHP's XML-based DOM doesn't allow
        // some characters. We have to coerce them sometimes.
        try {
            $attr = $target->createAttributeNS($namespace, $qualifiedName);
        } catch (\DOMException $e) {
            // The element name is invalid for XML
            // Replace any offending characters with "UHHHHHH" where H are the
            //   uppercase hexadecimal digits of the character's code point
            $attr = $target->createAttributeNS($namespace, $this->coerceName($qualifiedName));
        }

        if ($documentElement === null) {
            $attr = $this->cloneInnerNode($attr, $this->innerNode);
        }

        return $this->innerNode->getWrapperNode($attr);
    }


    public function __toString() {
        return $this->serialize();
    }
}