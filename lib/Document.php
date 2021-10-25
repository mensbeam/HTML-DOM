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
    use ParentNode;

    protected string $_contentType = 'text/html';
    protected DOMImplementation $_implementation;

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

    protected function __get_contentType(): string {
        return $this->_contentType;
    }

    protected function __get_documentElement(): ?Element {
        return $this->innerNode->getWrapperNode($this->innerNode->documentElement);
    }

    protected function __get_implementation(): DOMImplementation {
        return $this->_implementation;
    }


    public function __construct() {
        parent::__construct(new InnerDocument($this));
        $this->_implementation = Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\DOMImplementation', $this);
    }


    public function createCDATASection(): CDATASection {
        return $this->innerNode->getWrapperNode($this->innerNode->createCDATASection($data));
    }

    public function createComment(): Comment {
        return $this->innerNode->getWrapperNode($this->innerNode->createComment($data));
    }

    public function createDocumentFragment(): DocumentFragment {
        return $this->innerNode->getWrapperNode($this->innerNode->createDocumentFragment());
    }

    public function createElement(string $localName): Element {
        # 1. If localName does not match the Name production, then throw an
        #    "InvalidCharacterError" DOMException.
        if (!preg_match(InnerDocument::NAME_PRODUCTION_REGEX, $localName)) {
            throw new DOMException(DOMException::INVALID_CHARACTER);
        }

        # 2. If this is an HTML document, then set localName to localName in ASCII
        #    lowercase.
        if ($this instanceof Document && !$this instanceof XMLElement) {
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

    public function createProcessingInstruction(string $target, string $data): ProcessingInstruction {
        return $this->innerNode->getWrapperNode($this->innerNode->createProcessingInstruction($target, $data));
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