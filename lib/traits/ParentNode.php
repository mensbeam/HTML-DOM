<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use Symfony\Component\CssSelector\CssSelectorConverter,
    Symfony\Component\CssSelector\Exception\SyntaxErrorException as SymfonySyntaxErrorException;


# 4.2.6. Mixin ParentNode
trait ParentNode {
    use NodeTrait;


    protected function __get_children(): \DOMNodeList {
        # The children getter steps are to return an HTMLCollection collection rooted at
        # this matching only element children.
        // DEVIATION: HTMLCollection doesn't exist in PHP's DOM, and NodeList is
        // almost identical; so, using that. PHP's DOM doesn't provide the end user any
        // way to create a NodeList from scratch, so going to cheat and use XPath to
        // make one for us.
        $document = ($this instanceof Document) ? $this : $this->ownerDocument;
        return $document->xpath->query('child::*', $this);
    }


    public function appendChild($node) {
        $this->preInsertionValidity($node);
        $result = parent::appendChild($node);
        if ($result !== false && $node instanceof HTMLTemplateElement) {
            ElementMap::add($node);
        }
        return $node;
    }

    public function insertBefore($node, $child = null) {
        $this->preInsertionValidity($node, $child);

        $result = parent::insertBefore($node, $child);
        if ($result !== false && $node instanceof HTMLTemplateElement) {
            ElementMap::add($node);
        }
        return $node;
    }

    public function querySelector(string $selectors): ?Element {
        # The querySelector(selectors) method steps are to return the first result of
        # running scope-match a selectors string selectors against this, if the result
        # is not an empty list; otherwise null.
        $result = $this->scopeMatchSelector($selectors);
        return ($result !== null) ? $result[0] : null;
    }

    public function querySelectorAll(string $selectors): NodeList {
        # The querySelectorAll(selectors) method steps are to return the static result
        # of running scope-match a selectors string selectors against this.
        $nodeList = $this->scopeMatchSelector($selectors);
        return new NodeList($nodeList);
    }

    public function removeChild($child) {
        $result = parent::removeChild($child);
        if ($result !== false && $child instanceof Element) {
            ElementMap::delete($child);
        }
        return $child;
    }

    public function replaceChild($node, $child) {
        $result = parent::replaceChild($node, $child);

        if ($result !== false) {
            if ($node instanceof HTMLTemplateElement) {
                ElementMap::add($node);
            }
            if ($child instanceof Element) {
                ElementMap::delete($child);
            }
        }
        return $node;
    }

    public function replaceChildren(Node|string ...$nodes) {
        # The replaceChildren(nodes) method steps are:
        # 1. Let node be the result of converting nodes into a node given nodes and
        #    this’s node document.
        $node = $this->convertNodesToNode($nodes);
        # 2. Ensure pre-insertion validity of node into this before null.
        $this->preInsertionValidity($node);
        # 3. Replace all with node within this.
        #
        # To replace all with a node within a parent, run these steps:
        # 1. Let removedNodes be parent’s children.
        $removedNodes = $this->childNodes;
        # 2. Let addedNodes be the empty set.
        $addedNodes = [];
        # 3. If node is a DocumentFragment node, then set addedNodes to node’s children.
        if ($node instanceof DocumentFragment) {
            $addedNodes = $node->childNodes;
        }
        # 4. Otherwise, if node is non-null, set addedNodes to « node ».
        elseif ($node !== null) {
            $addedNodes = $node;
        }
        # 5. Remove all parent’s children, in tree order, with the suppress observers
        # flag set.
        // DEVIATION: There is no scripting in this implementation, so cannnot set
        // suppress observers flag.
        while ($this->hasChildNodes()) {
            $this->removeChild($this->firstChild);
        }
        # 6. If node is non-null, then insert node into parent before null with the
        # suppress observers flag set.
        // DEVIATION: There is no scripting in this implementation, so cannnot set
        // suppress observers flag.
        if ($node !== null) {
            $this->appendChild($node);
        }
        # 7. If either addedNodes or removedNodes is not empty, then queue a tree
        # mutation record for parent with addedNodes, removedNodes, null, and null.
        // DEVIATION: There is no scripting in this implementation
    }

    /**
     * Generator which walks down the DOM from the node the method is being run on.
     * Nonstandard.
     *
     * @param ?\Closure $filter - An optional callback function used to filter; if not provided the generator will
     *                            just yield every node.
     * @param bool $includeReferenceNode - An optional boolean flag which if true includes the reference node ($this) in
     *                                     the iteration.
     */
    public function walk(?\Closure $filter = null, bool $includeReferenceNode = false): \Generator {
        $node = ($includeReferenceNode && !$this instanceof DocumentFragment) ? $this : $this->firstChild;

        if ($node !== null) {
            do {
                $next = $node->nextSibling;
                $result = ($filter === null) ? true : $filter($node);
                // Have to do type checking here because PHP is lacking in advanced typing
                if ($result !== true && $result !== false && $result !== null) {
                    $type = gettype($result);
                    if ($type === 'object') {
                        $type = get_class($result);
                    }
                    throw new Exception(Exception::CLOSURE_RETURN_TYPE_ERROR, '?bool', $type);
                }

                if ($result === true) {
                    yield $node;
                }

                // If the filter returns true (accept) or false (skip) and the node wasn't
                // removed in the filter iterate through the children
                if ($result !== null && $node->parentNode !== null) {
                    if ($node instanceof HTMLTemplateElement) {
                        $node = $node->content;
                    }

                    if ($node->hasChildNodes()) {
                        yield from $node->walk($filter);
                    }
                }
            } while ($node = $next);
        }
    }


    private function preInsertionValidity(\DOMDocumentType|Node $node, \DOMDocumentType|Node $child = null) {
        // "parent" in the spec comments below is $this

        # 1. If parent is not a Document, DocumentFragment, or Element node, then throw
        #    a "HierarchyRequestError" DOMException.
        // Not necessary because they've been disabled and return hierarchy request
        // errors in Node trait.

        # 2. If node is a host-including inclusive ancestor of parent, then throw a
        #    "HierarchyRequestError" DOMException.
        #
        # An object A is a host-including inclusive ancestor of an object B, if either
        # A is an inclusive ancestor of B, or if B’s root has a non-null host and A is a
        # host-including inclusive ancestor of B’s root’s host.
        if ($node->parentNode !== null) {
            if ($this->parentNode !== null && ($this === $node || $this->moonwalk(function($n) use($node) {
                return ($n === $node);
            })->current() !== null)) {
                throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
            } else {
                $parentRoot = $this->getRootNode();
                if ($parentRoot instanceof DocumentFragment) {
                    $parentRootHost = $parentRoot->host;
                    if ($parentRootHost !== null && ($parentRootHost === $node || $parentRootHost->moonwalk(function($n) use ($node) {
                        return ($n === $node);
                    })->current() !== null)) {
                        throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                    }
                }
            }
        }

        # 3. If child is non-null and its parent is not parent, then throw a
        #    "NotFoundError" DOMException.
        if ($child !== null && ($child->parentNode === null || $child->parentNode !== $this)) {
            throw new DOMException(DOMException::NOT_FOUND);
        }

        # 4. If node is not a DocumentFragment, DocumentType, Element, Text,
        #    ProcessingInstruction, or Comment node, then throw a "HierarchyRequestError"
        #    DOMException.
        if (!$node instanceof DocumentFragment && !$node instanceof \DOMDocumentType && !$node instanceof Element && !$node instanceof Text && !$node instanceof ProcessingInstruction && !$node instanceof Comment) {
            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
        }

        # 5. If either node is a Text node and parent is a document, or node is a
        #    doctype and parent is not a document, then throw a "HierarchyRequestError"
        #    DOMException.
        if (($node instanceof Text && $this instanceof Document) || ($node instanceof \DOMDocumentType && !$this instanceof Document)) {
            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
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
                    throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                } elseif ($nodeChildElementCount === 1) {
                    if ($this->childElementCount > 0 || $child instanceof \DOMDocumentType) {
                        throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                    }

                    if ($child !== null) {
                        $n = $child;
                        while ($n = $n->nextSibling) {
                            if ($n instanceof \DOMDocumentType) {
                                throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                            }
                        }
                    }
                }
            }
            # element
            #    parent has an element child, child is a doctype, or child is non-null and a
            #    doctype is following child.
            elseif ($node instanceof Element) {
                if ($child instanceof \DOMDocumentType) {
                    throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                }

                if ($child !== null) {
                    $n = $child;
                    while ($n = $n->nextSibling) {
                        if ($n instanceof \DOMDocumentType) {
                            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                        }
                    }
                }

                foreach ($this->childNodes as $c) {
                    if ($c instanceof Element) {
                        throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                    }
                }
            }

            # doctype
            #    parent has a doctype child, child is non-null and an element is preceding
            #    child, or child is null and parent has an element child.
            elseif ($node instanceof \DOMDocumentType) {
                foreach ($this->childNodes as $c) {
                    if ($c instanceof \DOMDocumentType) {
                        throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                    }
                }

                if ($child !== null) {
                    $n = $child;
                    while ($n = $n->previousSibling) {
                        if ($n instanceof Element) {
                            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                        }
                    }
                } else {
                    foreach ($this->childNodes as $c) {
                        if ($c instanceof Element) {
                            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                        }
                    }
                }
            }
        }
    }

    private function scopeMatchSelector(string $selectors): ?\DOMNodeList {
        # To scope-match a selectors string selectors against a node, run these steps:
        # 1. Let s be the result of parse a selector selectors. [SELECTORS4]
        // This implementation will instead convert the CSS selector to an XPath query
        // using Symfony's CSS selector converter library.
        try {
            $converter = new CssSelectorConverter();
            $s = $converter->toXPath($selectors);
        } catch (\Exception $e) {
            # 2. If s is failure, then throw a "SyntaxError" DOMException.
            // Symfony's library will throw an exception if something is unsupported, too,
            // so only throw exception when an actual syntax error, otherwise return null.
            if ($e instanceof SymfonySyntaxErrorException) {
                throw new DOMException(DOMException::SYNTAX_ERROR);
            }

            return null;
        }

        # 3. Return the result of match a selector against a tree with s and node’s root
        #    using scoping root node. [SELECTORS4].
        $doc = ($this instanceof Document) ? $this : $this->ownerDocument;
        $nodeList = $doc->xpath->query($s, $this);
        if ($nodeList->length === 0) {
            return null;
        }

        return $nodeList;
    }
}