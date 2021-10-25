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


class Document extends Node {
    use DocumentOrElement, ParentNode;

    protected string $_compatMode = 'CSS1Compat';
    protected string $_contentType = 'text/html';
    protected DOMImplementation $_implementation;
    protected string $_URL = '';

    protected function __get_body(): Element {
        if ($this->documentElement === null || !$this->documentElement->hasChildNodes()) {
            return null;
        }

        # The body element of a document is the first of the html element's children
        # that is either a body element or a frameset element, or null if there is no
        # such element.
        return $this->documentElement->firstChild->walkFollowing(function($n) {
            $name = strtolower($n->nodeName);
            return ($n instanceof Element && $n->namespaceURI === Parser::HTML_NAMESPACE && ($name === 'body' || $name === 'frameset'));
        }, true)->current();
    }

    protected function __get_compatMode(): string {
        return $this->_compatMode;
    }

    protected function __get_contentType(): string {
        return $this->_contentType;
    }

    protected function __get_documentElement(): ?Element {
        return $this->innerNode->getWrapperNode($this->innerNode->documentElement);
    }

    protected function __get_documentURI(): string {
        return $this->_URL;
    }

    protected function __get_implementation(): DOMImplementation {
        return $this->_implementation;
    }

    protected function __get_URL(): string {
        return $this->_URL;
    }


    public function __construct() {
        parent::__construct(new InnerDocument($this));
        $this->_implementation = Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\DOMImplementation', $this);
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

        # 3. Return a new attribute whose local name is localName and node document is
        #    this.
        // We need to do a couple more things here. PHP's XML-based DOM doesn't allow
        // some characters. We have to coerce them sometimes.
        try {
            $attr = $target->createAttributeNS(null, $localName);
        } catch (\DOMException $e) {
            // The element name is invalid for XML
            // Replace any offending characters with "UHHHHHH" where H are the
            //   uppercase hexadecimal digits of the character's code point
            $attr = $target->createAttributeNS(null, $this->coerceName($localName));
        }

        if ($documentElement === null) {
            return $this->importNode($attr);
        }

        return $this->innerNode->getWrapperNode($attr);
    }

    public function createAttributeNS(string $namespace, string $qualifiedName): Attr {
        # The createAttributeNS(namespace, qualifiedName) method steps are:
        #
        # 1. Let namespace, prefix, and localName be the result of passing namespace and
        #    qualifiedName to validate and extract.
        [ 'namespace' => $namespace, 'prefix' => $prefix, 'localName' => $localName ] = $this->validateAndExtract($qualifiedName, $namespace);
        $qualifiedName = ($prefix) ? "$prefix:$localName" : $localName;

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
            $attr = $target->createAttributeNS($namespace, $this->coerceName($qualifiedName));
        }

        if ($documentElement === null) {
            return $this->importNode($attr);
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
            if ($namespace !== null) {
                $qualifiedName = implode(':', array_map([ $this, 'coerceName' ], explode(':', $qualifiedName, 2)));
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

    public function importNode(\DOMNode|Node $node, bool $deep = false): Node {
        $isDOMNode = ($node instanceof \DOMNode);
        $node = $this->innerNode->getWrapperNode($this->innerNode->importNode((!$isDOMNode) ? $this->getInnerNode($node) : $node, false));
        if ($node instanceof Element || $node instanceof DocumentFragment) {
            $this->convertAdoptedOrImportedNode($node, $isDOMNode);
        }

        return $node;
    }


    protected function convertAdoptedOrImportedNode(Node $node, bool $originalWasDOMNode = false): Node {
        // Yet another PHP DOM hang-up that is either a bug or a feature. When
        // elements are imported their id attributes aren't able to be picked up by
        // getElementById, so let's fix that.
        $elementsWithIds = $node->walk(function($n) {
            return ($n instanceof Element && $n->hasAttribute('id'));
        }, true);

        foreach ($elementsWithIds as $e) {
            // setIdAttributeNode doesn't exist in modern DOM so isn't exposed in the
            // wrapper element.
            $e = $this->getInnerNode($e);
            $e->setIdAttributeNode($e->getAttributeNode('id'), true);
        }

        // If the orginal node was a DOMNode then all child nodes of template elements
        // need to be inserted into the template's content DocumentFragment.
        if ($originalWasDOMNode) {
            // Yes, it seems weird to unpack a generator like this, but there is a need to
            // iterate through them in reverse so nested templates can be handled properly.
            // Also, this is slightly faster than using XPath to look for the templates;
            // they would also need to be unpacked because the NodeList is live and would
            // create an infinite loop if not unpacked to an array.
            $templates = [ ...$node->walk(function($n) {
                return ($n instanceof Element && !$n instanceof HTMLTemplateElement && $n->namespaceURI === Parser::HTML_NAMESPACE && strtolower($n->nodeName) === 'template');
            }, true) ];

            for ($templatesLength = count($templates), $i = $templatesLength - 1; $i >= 0; $i--) {
                $template = $templates[$i];
                while ($template->hasChildNodes()) {
                    $template->content->appendChild($template->firstChild);
                }
            }
        }

        return $node;
    }
}