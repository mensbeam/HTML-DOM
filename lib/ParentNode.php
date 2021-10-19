<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\Framework\MagicProperties;


abstract class Node {
    use MagicProperties;

    public const ELEMENT_NODE = 1;
    public const ATTRIBUTE_NODE = 2;
    public const TEXT_NODE = 3;
    public const CDATA_SECTION_NODE = 4;
    public const ENTITY_REFERENCE_NODE = 5; // legacy
    public const ENTITY_NODE = 6; // legacy
    public const PROCESSING_INSTRUCTION_NODE = 7;
    public const COMMENT_NODE = 8;
    public const DOCUMENT_MODE = 9;
    public const DOCUMENT_TYPE_NODE = 10;
    public const DOCUMENT_FRAGMENT_NODE = 11;
    public const NOTATION_NODE = 12; // legacy

    public const DOCUMENT_POSITION_DISCONNECTED = 0x01;
    public const DOCUMENT_POSITION_PRECEDING = 0x02;
    public const DOCUMENT_POSITION_FOLLOWING = 0x04;
    public const DOCUMENT_POSITION_CONTAINS = 0x08;
    public const DOCUMENT_POSITION_CONTAINED_BY = 0x10;
    public const DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC = 0x20;

    protected \DOMNode $innerNode;

    /**
     * The nodeName read-only property returns the name of the current Node as a
     * string.
     *
     * @property-read string nodeName
     */
    protected function __get_nodeName(): string {
        # The nodeName getter steps are to return the first matching statement,
        # switching on the interface this implements:

        # ↪ Element
        #     Its HTML-uppercased qualified name.
        if ($this instanceof Element) {
            return strtoupper($this->innerNode->nodeName);
        }

        // PHP's DOM mostly does this correctly with the exception of Element, so let's
        // fall back to PHP's DOM on everything else.
        return $this->innerNode->nodeName;
    }

    /**
     * The read-only Node.nodeType property is an integer that identifies what the
     * node is. It distinguishes different kind of nodes from each other, such as
     * elements, text and comments.
     *
     * @property-read int nodeType
     */
    protected function __get_nodeType(): int {
        // PHP's DOM does this correctly already.
        return $this->innerNode->nodeType;
    }

    /**
     * The ownerDocument read-only property of the Node interface returns the
     * top-level document object of the node.
     *
     * @property-read Document ownerDocument
     */
    protected function __get_ownerDocument(): Document {
        if ($this instanceof Document) {
            return $this;
        }

        return $this->innerNode->ownerDocument->getWrapperNode();
    }

    /**
     * The Node.parentElement read-only property returns the DOM node's parent
     * Element, or null if the node either has no parent, or its parent isn't a DOM
     * Element.
     *
     * @property-read ?Element parentElement
     */
    protected function __get_parentElement(): ?Element {
        # The parentElement getter steps are to return this’s parent element.
        # A node’s parent of type Element is known as its parent element. If the node
        # has a parent of a different type, its parent element is null.
        $parent = $this->parentNode;
        return ($parent instanceof Element) ? $parent : null;
    }

    /**
     * The Node.parentNode read-only property returns the parent of the specified
     * node in the DOM tree.
     *
     * @property-read ?Node parentNode
     */
    protected function __get_parentNode(): ?Node {
        # The parentNode getter steps are to return this’s parent.
        # An object that participates in a tree has a parent, which is either null or an
        # object, and has children, which is an ordered set of objects. An object A
        # whose parent is object B is a child of B.

        if ($this instanceof Document) {
            return null;
        }

        $parent = $this->innerNode->parentNode;
        if ($parent === null) {
            return null;
        }

        return $parent->ownerDocument->getWrapperNode($parent);
    }


    protected function __construct(\DOMNode $innerNode) {
        $this->innerNode = $innerNode;
    }
}
