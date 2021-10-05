<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


# 4.2.6. Mixin ParentNode
trait ParentNode {
    use Node, ParentNodePolyfill;


    protected function __get_children(): \DOMNodeList {
        # The children getter steps are to return an HTMLCollection collection rooted at
        # this matching only element children.
        // DEVIATION: HTMLCollection doesn't exist in PHP's DOM, and \DOMNodeList is
        // almost identical; so, using that. PHP's DOM doesn't provide the end user any
        // way to create a \DOMNodeList from scratch, so going to cheat and use XPath to
        // make one for us.

        $isDocument = ($this instanceof Document);
        $document = ($isDocument) ? $this : $this->ownerDocument;
        return $document->xpath->query('//*', (!$isDocument) ? $this : null);
    }


    public function appendChild($node) {
        $this->preInsertionValidity($node);

        $result = parent::appendChild($node);
        if ($result !== false && $result instanceof HTMLTemplateElement) {
            ElementMap::add($result);
        }
        return $result;
    }

    public function insertBefore($node, $child = null) {
        $this->preInsertionValidity($node, $child);

        $result = parent::insertBefore($node, $child);
        if ($result !== false) {
            if ($result instanceof HTMLTemplateElement) {
                ElementMap::add($result);
            }
            if ($child instanceof HTMLTemplateElement) {
                ElementMap::delete($child);
            }
        }
        return $result;
    }

    public function removeChild($child) {
        $result = parent::removeChild($child);
        if ($result !== false && $result instanceof HTMLTemplateElement) {
            ElementMap::delete($child);
        }
        return $result;
    }

    public function replaceChild($node, $child) {
        $result = parent::replaceChild($node, $child);
        if ($result !== false) {
            if ($result instanceof HTMLTemplateElement) {
                ElementMap::add($child);
            }
            if ($child instanceof HTMLTemplateElement) {
                ElementMap::delete($child);
            }
        }
        return $result;
    }

    public function replaceChildren(...$nodes) {
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
            $addedNodes = node;
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


    protected function preInsertionValidity(\DOMNode $node, ?\DOMNode $child = null) {
        // "parent" in the spec comments below is $this

        # 1. If parent is not a Document, DocumentFragment, or Element node, then throw
        #    a "HierarchyRequestError" DOMException.
        // Not necessary because they've been disabled and return hierarchy request
        // errors in ChildNode trait.

        # 2. If node is a host-including inclusive ancestor of parent, then throw a
        #    "HierarchyRequestError" DOMException.
        #
        # An object A is a host-including inclusive ancestor of an object B, if either
        # A is an inclusive ancestor of B, or if B’s root has a non-null host and A is a
        # host-including inclusive ancestor of B’s root’s host.
        if ($node->parentNode !== null) {
            if ($this->isSameNode($node) || $this->moonwalk(function($n) use($node) {
                if ($n->isSameNode($node)) {
                    return true;
                }
            })->current() !== null) {
                throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
            } else {
                $parentRoot = $this->getRootNode();
                $parentRootHost = $parentRoot->host;
                if ($parentRoot instanceof DocumentFragment && $parentRootHost !== null && ($host->isSameNode($node) || $host->moonwalk(function($n) use($node) {
                    if ($n->isSameNode($node)) {
                        return true;
                    }
                })->current() !== null)) {
                    throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                }
            }
        }

        # 3. If child is non-null and its parent is not parent, then throw a
        #    "NotFoundError" DOMException.
        if ($child !== null && ($child->parentNode === null || !$child->parentNode->isSameNode($this))) {
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
        // Not necessary because they've been disabled and return hierarchy request
        // errors in ChildNode trait

        # 6. If parent is a document, and any of the statements below, switched on node,
        #    are true, then throw a "HierarchyRequestError" DOMException.
        if ($this instanceof Document) {
            # DocumentFragment node
            #    If node has more than one element child or has a Text node child.
            #    Otherwise, if node has one element child and either parent has an element
            #    child, child is a doctype, or child is non-null and a doctype is following
            #    child.
            if ($node instanceof \DOMDocumentType) {
                if ($node->childNodes->length > 1 || $node->firstChild instanceof Text) {
                    throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                } else {
                    if ($node->firstChild instanceof \DOMDocumentType) {
                        throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                    }

                    foreach ($this->childNodes as $c) {
                        if ($c instanceof Element) {
                            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                        }
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
                    while ($n = $n->prevSibling) {
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


    private function convertNodesToNode(array $nodes): \DOMNode {
        # To convert nodes into a node, given nodes and document, run these steps:
        # 1. Let node be null.
        # 2. Replace each string in nodes with a new Text node whose data is the string
        #    and node document is document.
        # 3. If nodes contains one node, then set node to nodes[0].
        # 4. Otherwise, set node to a new DocumentFragment node whose node document is
        #    document, and then append each node in nodes, if any, to it.
        // The spec would have us iterate through the provided nodes and then iterate
        // through them again to append. Let's optimize this a wee bit, shall we?
        $document = ($this instanceof Document) ? $this : $this->ownerDocument;
        $node = ($node->length > 1) ? $document->createDocumentFragment() : null;
        foreach ($nodes as &$n) {
            // Can't do union types until PHP 8... OTL
            if (!$n instanceof \DOMNode && !is_string($n)) {
                trigger_error(sprintf("Uncaught TypeError: %s::%s(): Argument #1 (\$%s) must be of type \DOMNode|string, %s given", __CLASS__, __METHOD__, 'nodes', gettype($n)));
            }

            if (is_string($n)) {
                $n = $this->ownerDocument->createTextNode($n);
            }

            if ($node !== null) {
                $node->appendChild($n);
            } else {
                $node = $n;
            }
        }

        return $node;
    }
}