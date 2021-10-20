<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\Framework\MagicProperties,
    MensBeam\HTML\DOM\InnerNode\Factory;


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

    protected function __get_childNodes(): NodeList {
        // NodeLists cannot be created from their constructors normally.
        // DEVIATION: Going to optimize here and only create a truly live NodeList if
        // the object is even capable of having children, otherwise will just be an
        // empty NodeList. There is no sense in generating a live list that will never
        // update.
        return Factory::createFromProtectedConstructor(__NAMESPACE__ . '\\NodeList', ($this instanceof Document || $this instanceof DocumentFragment || $this instanceof Element) ? function() {
            $result = [];
            $innerChildNodes = $this->innerNode->childNodes;
            foreach ($innerChildNodes as $i) {
                $result[] = $this->innerNode->ownerDocument->getWrapperNode($i);
            }

            return $result;
        } : []);

        return $nodeList;
    }

    protected function __get_firstChild(): ?Node {
        // PHP's DOM does this effectively already.
        return $this->innerNode->firstChild;
    }

    protected function __get_lastChild(): ?Node {
        // PHP's DOM does this effectively already.
        return $this->innerNode->lastChild;
    }

    protected function __get_previousSibling(): ?Node {
        // PHP's DOM does this effectively already.
        return $this->innerNode->previousSibling;
    }

    protected function __get_nextSibling(): ?Node {
        // PHP's DOM does this effectively already.
        return $this->innerNode->nextSibling;
    }

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

    protected function __get_nodeType(): int {
        // PHP's DOM does this correctly already.
        return $this->innerNode->nodeType;
    }

    protected function __get_ownerDocument(): Document {
        if ($this instanceof Document) {
            return $this;
        }

        return $this->innerNode->ownerDocument->getWrapperNode();
    }

    protected function __get_parentElement(): ?Element {
        # The parentElement getter steps are to return this’s parent element.
        # A node’s parent of type Element is known as its parent element. If the node
        # has a parent of a different type, its parent element is null.
        $parent = $this->parentNode;
        return ($parent instanceof Element) ? $parent : null;
    }

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


    public function appendChild(Node $node): Node {
        # The appendChild(node) method steps are to return the result of appending node to
        # this.
        $this->preInsertionValidity($node);
        $inner = $this->getInnerNode($node);
        $this->innerNode->appendChild($inner);
        return $node;
    }

    public function contains(?Node $other): bool {
        # The contains(other) method steps are to return true if other is an inclusive
        # descendant of this; otherwise false (including when other is null).
        return ($other->moonWalk(function($n) use($other) {
            return ($n === $other);
        })->current() !== null);
    }

    public function hasChildNodes(): bool {
        return $this->innerNode->hasChildNodes();
    }


    protected function getInnerNode(Node $node) {
        if ($node === $this) {
            return $this->innerNode;
        }

        $reflector = new \ReflectionClass($node::class);
        $innerNode = new \ReflectionProperty($node, 'innerNode');
        $innerNode->setAccessible(true);
        return $innerNode->getValue($node);
    }


    private function preInsertionValidity(Node $node, ?Node $child = null) {
        // "parent" in the spec comments below is $this

        # 1. If parent is not a Document, DocumentFragment, or Element node, then throw
        #    a "HierarchyRequestError" Exception.
        if (!$this instanceof Document && !$this instanceof DocumentFragment && !$this instanceof Element) {
            throw new Exception(Exception::HIERARCHY_REQUEST_ERROR);
        }

        # 2. If node is a host-including inclusive ancestor of parent, then throw a
        #    "HierarchyRequestError" Exception.
        #
        # An object A is a host-including inclusive ancestor of an object B, if either
        # A is an inclusive ancestor of B, or if B’s root has a non-null host and A is a
        # host-including inclusive ancestor of B’s root’s host.
        if ($node->parentNode !== null) {
            if ($this->parentNode !== null && ($this === $node || $node->contains($this))) {
                throw new Exception(Exception::HIERARCHY_REQUEST_ERROR);
            } else {
                $parentRoot = $this->getRootNode();
                if ($parentRoot instanceof DocumentFragment) {
                    $parentRootHost = $parentRoot->host;
                    if ($parentRootHost !== null && ($parentRootHost === $node || $node->contains($parentRootHost))) {
                        throw new Exception(Exception::HIERARCHY_REQUEST_ERROR);
                    }
                }
            }
        }

        # 3. If child is non-null and its parent is not parent, then throw a
        #    "NotFoundError" Exception.
        if ($child !== null && ($child->parentNode === null || $child->parentNode !== $this)) {
            throw new Exception(Exception::NOT_FOUND);
        }

        # 4. If node is not a DocumentFragment, DocumentType, Element, Text,
        #    ProcessingInstruction, or Comment node, then throw a "HierarchyRequestError"
        #    Exception.
        if (!$node instanceof DocumentFragment && !$node instanceof DocumentType && !$node instanceof Element && !$node instanceof Text && !$node instanceof ProcessingInstruction && !$node instanceof Comment) {
            throw new Exception(Exception::HIERARCHY_REQUEST_ERROR);
        }

        # 5. If either node is a Text node and parent is a document, or node is a
        #    doctype and parent is not a document, then throw a "HierarchyRequestError"
        #    Exception.
        if (($node instanceof Text && $this instanceof Document) || ($node instanceof DocumentType && !$this instanceof Document)) {
            throw new Exception(Exception::HIERARCHY_REQUEST_ERROR);
        }

        # 6. If parent is a document, and any of the statements below, switched on the
        #    interface node implements, are true, then throw a "HierarchyRequestError".
        if ($this instanceof Document) {
            # DocumentFragment node
            #    If node has more than one element child or has a Text node child.
            #    Otherwise, if node has one element child and either parent has an element
            #    child, child is a doctype, or child is non-null and a doctype is following
            #    child.
            if ($node instanceof DocumentFragment) {
                $nodeChildElementCount = $node->childElementCount;
                if ($nodeChildElementCount > 1 || $node->firstChild->walkFollowing(function($n) {
                    return ($n instanceof Text);
                }, true)->current() !== null) {
                    throw new Exception(Exception::HIERARCHY_REQUEST_ERROR);
                } elseif ($nodeChildElementCount === 1) {
                    if ($this->childElementCount > 0 || $child instanceof DocumentType) {
                        throw new Exception(Exception::HIERARCHY_REQUEST_ERROR);
                    }

                    if ($child !== null) {
                        $n = $child;
                        while ($n = $n->nextSibling) {
                            if ($n instanceof DocumentType) {
                                throw new Exception(Exception::HIERARCHY_REQUEST_ERROR);
                            }
                        }
                    }
                }
            }
            # element
            #    parent has an element child, child is a doctype, or child is non-null and a
            #    doctype is following child.
            elseif ($node instanceof Element) {
                if ($child instanceof DocumentType) {
                    throw new Exception(Exception::HIERARCHY_REQUEST_ERROR);
                }

                if ($child !== null) {
                    $n = $child;
                    while ($n = $n->nextSibling) {
                        if ($n instanceof DocumentType) {
                            throw new Exception(Exception::HIERARCHY_REQUEST_ERROR);
                        }
                    }
                }

                foreach ($this->childNodes as $c) {
                    if ($c instanceof Element) {
                        throw new Exception(Exception::HIERARCHY_REQUEST_ERROR);
                    }
                }
            }

            # doctype
            #    parent has a doctype child, child is non-null and an element is preceding
            #    child, or child is null and parent has an element child.
            elseif ($node instanceof DocumentType) {
                foreach ($this->childNodes as $c) {
                    if ($c instanceof DocumentType) {
                        throw new Exception(Exception::HIERARCHY_REQUEST_ERROR);
                    }
                }

                if ($child !== null) {
                    $n = $child;
                    while ($n = $n->previousSibling) {
                        if ($n instanceof Element) {
                            throw new Exception(Exception::HIERARCHY_REQUEST_ERROR);
                        }
                    }
                } else {
                    foreach ($this->childNodes as $c) {
                        if ($c instanceof Element) {
                            throw new Exception(Exception::HIERARCHY_REQUEST_ERROR);
                        }
                    }
                }
            }
        }
    }
}
