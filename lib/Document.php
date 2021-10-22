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

    protected function __get_contentType(): string {
        return $this->_contentType;
    }

    protected function __get_implementation(): DOMImplementation {
        return $this->_implementation;
    }


    public function __construct() {
        parent::__construct(new InnerDocument($this));
        $this->_implementation = Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\DOMImplementation', $this);
    }


    public function createDocumentFragment(): DocumentFragment {
        return $this->innerNode->getWrapperNode($this->innerNode->createDocumentFragment());
    }

    public function createElement(string $localName): Element {
        return $this->innerNode->getWrapperNode($this->innerNode->createElement($localName));
    }

    public function createTextNode(string $data): Text {
        // Text has a public constructor that creates an inner text node without an
        // associated document, so some jiggerypokery must be done instead.
        $reflector = new \ReflectionClass(__NAMESPACE__ . '\\Text');
        $text = $reflector->newInstanceWithoutConstructor();
        $property = new \ReflectionProperty($text, 'innerNode');
        $property->setAccessible(true);
        $property->setValue($text, $this->innerNode->createTextNode($data));
        return $text;
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