<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


trait NodeTrait {
    private static ?int $rand = null;


    // Disable C14N
    public function C14N($exclusive = null, $with_comments = null, ?array $xpath = null, ?array $ns_prefixes = null): bool {
        throw new Exception(Exception::DISABLED_METHOD, __METHOD__, 'It is meant for XML and buggy; use Document::saveHTML or cast to a string');
    }

    // Disable C14NFile
    public function C14NFile($uri, $exclusive = null, $with_comments = null, ?array $xpath = null, ?array $ns_prefixes = null): bool {
        throw new Exception(Exception::DISABLED_METHOD, __METHOD__, 'It is meant for XML and buggy; use Document::saveHTML or cast to a string');
    }

    public function compareDocumentPosition(Node $other): int {
        # The compareDocumentPosition(other) method steps are:
        #
        # 1. If this is other, then return zero.
        if ($this === $other) {
            return 0;
        }

        # 2. Let node1 be other and node2 be this.
        $node1 = $other;
        $node2 = $this;

        # 3. Let attr1 and attr2 be null.
        $attr1 = $attr2 = null;

        # 4. If node1 is an attribute, then set attr1 to node1 and node1 to attr1’s
        #   element.
        if ($node1 instanceof Attr) {
            $attr1 = $node1;
            $node1 = $attr1->ownerElement;
        }

        # 5. If node2 is an attribute, then:
        if ($node2 instanceof Attr) {
            # 1. Set attr2 to node2 and node2 to attr2’s element.
            $attr2 = $node2;
            $node2 = $attr2->ownerElement;

            # 2. If attr1 and node1 are non-null, and node2 is node1, then:
            if ($attr1 !== null && $node1 !== null && $node2 === $node1) {
                # 1. For each attr in node2’s attribute list:
                foreach ($node2->attributes as $attr) {
                    # 1. If attr equals attr1, then return the result of adding DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC and DOCUMENT_POSITION_PRECEDING.
                    if ($attr === $attr1) {
                        return Node::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC + Node::DOCUMENT_POSITION_PRECEDING;
                    }

                    # 2. If attr equals attr2, then return the result of adding DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC and DOCUMENT_POSITION_FOLLOWING.
                    if ($attr === $attr2) {
                        return Node::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC + Node::DOCUMENT_POSITION_FOLLOWING;
                    }
                }
            }
        }

        # 6. If node1 or node2 is null, or node1’s root is not node2’s root, then return the
        #    result of adding DOCUMENT_POSITION_DISCONNECTED,
        #    DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC, and either
        #    DOCUMENT_POSITION_PRECEDING or DOCUMENT_POSITION_FOLLOWING, with the constraint
        #    that this is to be consistent, together.
        #
        # NOTE: Whether to return DOCUMENT_POSITION_PRECEDING or
        # DOCUMENT_POSITION_FOLLOWING is typically implemented via pointer comparison.
        # In JavaScript implementations a cached Math.random() value can be used.
        if (self::$rand === null) {
            self::$rand = rand(0, 1);
        }

        if ($node1 === null || $node2 === null || $node1->getRootNode() !== $node2->getRootNode()) {
            return Node::DOCUMENT_POSITION_DISCONNECTED + Node::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC + ((self::$rand === 0) ? Node::DOCUMENT_POSITION_PRECEDING : Node::DOCUMENT_POSITION_FOLLOWING);
        }

        # 7. If node1 is an ancestor of node2 and attr1 is null, or node1 is node2 and attr2
        #    is non-null, then return the result of adding DOCUMENT_POSITION_CONTAINS to
        #    DOCUMENT_POSITION_PRECEDING.
        if (($node1 === $node2 && $attr2 !== null) || ($attr1 === null && $node2->moonwalk(function($n) use($node1) {
            return ($n === $node1);
        })->current() !== null)) {
            return Node::DOCUMENT_POSITION_CONTAINS + Node::DOCUMENT_POSITION_PRECEDING;
        }

        # 8. If node1 is a descendant of node2 and attr2 is null, or node1 is node2 and attr1
        #    is non-null, then return the result of adding DOCUMENT_POSITION_CONTAINED_BY to
        #    DOCUMENT_POSITION_FOLLOWING.
        if (($node1 === $node2 && $attr1 !== null) || ($attr2 === null && $node2->walk(function($n) use($node1) {
            return ($n === $node1);
        })->current() !== null)) {
            return Node::DOCUMENT_POSITION_CONTAINED_BY + Node::DOCUMENT_POSITION_FOLLOWING;
        }

        # 9. If node1 is preceding node2, then return DOCUMENT_POSITION_PRECEDING.
        if ($node2->walkPreceding(function($n) use($node1) {
            return ($n === $node1);
        })->current() !== null) {
            return Node::DOCUMENT_POSITION_PRECEDING;
        }

        # 10. Return DOCUMENT_POSITION_FOLLOWING.
        return Node::DOCUMENT_POSITION_FOLLOWING;
    }

    public function contains(\DOMDocumentType|Node|null $other): bool {
        # The contains(other) method steps are to return true if other is an inclusive
        # descendant of this; otherwise false (including when other is null).
        // The spec is remarkably vague about this method, so I'm going to do some
        // additional time saving checks.
        if ($other === null || $other->parentNode === null || $other instanceof Attr || $other instanceof Document || $other instanceof DocumentFragment || (!$this instanceof Document && !$this instanceof DocumentFragment && !$this instanceof Element)) {
            return false;
        }

        $thisDoc = ($this instanceof Document) ? $this : $this->ownerDocument;
        if ($thisDoc !== $other->ownerDocument) {
            return false;
        }

        return ($this->walk(function($n) use($other) {
            return ($n === $other);
        })->current() !== null);
    }

    public function isEqualNode(\DOMDocumentType|Node $otherNode): bool {
        # The isEqualNode(otherNode) method steps are to return true if otherNode is
        # non-null and this equals otherNode; otherwise false.

        # A node A equals a node B if all of the following conditions are true:
        #
        # • A and B implement the same interfaces.
        if ($this::class !== $otherNode::class) {
            return false;
        }

        # • The following are equal, switching on the interface A implements:
        $thisClass = substr($this::class, strrpos($this::class, '\\') + 1);
        switch ($thisClass) {
            # - DocumentType
            #     Its name, public ID, and system ID.
            // DEVIATION: $this can never be a \DOMDocumentType seeing as we we cannot extend
            // \DOMDocumentType, so there is no need to check for it.

            # - Element
            #   Its namespace, namespace prefix, local name, and its attribute list’s size.
            // PCOV is stupid
            // @codeCoverageIgnoreStart
            case 'Element':
            // @codeCoverageIgnoreEnd
                if ($this->namespaceURI !== $otherNode->namespaceURI || $this->prefix !== $otherNode->prefix || $this->localName !== $otherNode->localName || $this->attributes->length !== $otherNode->attributes->length) {
                    return false;
                }

                # • If A is an element, each attribute in its attribute list has an attribute that
                #   equals an attribute in B’s attribute list.
                foreach ($this->attributes as $key => $attr) {
                    if (!$attr->isEqualNode($otherNode->attributes[$key])) {
                        return false;
                    }
                }
            break;
            # - Attr
            #     Its namespace, local name, and value.
            // PCOV is stupid
            // @codeCoverageIgnoreStart
            case 'Attr':
            // @codeCoverageIgnoreEnd
                if ($this->namespaceURI !== $otherNode->namespaceURI || $this->localName !== $otherNode->localName || $this->value !== $otherNode->value) {
                    return false;
                }
            break;
            # - Text
            # - Comment
            #   Its data.
            // PCOV is stupid
            // @codeCoverageIgnoreStart
            case 'Text':
            case 'Comment':
            // @codeCoverageIgnoreEnd
                if ($this->data !== $otherNode->data) {
                    return false;
                }
            break;
        }

        if ($this instanceof Document || $this instanceof DocumentFragment || $this instanceof Element) {
            # • A and B have the same number of children.
            if ($this->childNodes->length !== $otherNode->childNodes->length) {
                return false;
            }

            # • Each child of A equals the child of B at the identical index.
            foreach ($this->childNodes as $key => $child) {
                // Have to work around the fact we cannot extend \DOMDocumentType
                if (!$child instanceof \DOMDocumentType) {
                    if (!$child->isEqualNode($otherNode->childNodes[$key])) {
                        return false;
                    }
                } else {
                    $other = $otherNode->childNodes[$key];
                    if ($child->name !== $other->name || $child->publicId !== $other->publicId || $child->systemId !== $other->systemId) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    // Disable getLineNo
    public function getLineNo(): int {
        throw new Exception(Exception::DISABLED_METHOD, __METHOD__, 'It is meant for XML and buggy; use Document::saveHTML or cast to a string');
    }

    public function getRootNode(): ?Node {
        # The getRootNode(options) method steps are to return this’s shadow-including
        # root if options["composed"] is true; otherwise this’s root.
        // DEVIATION: This implementation does not have scripting, so there's no Shadow
        // DOM. Therefore, there isn't a need for the options parameter.

        # The root of an object is itself, if its parent is null, or else it is the root
        # of its parent. The root of a tree is any object participating in that tree
        # whose parent is null.
        if ($this->parentNode === null) {
            return $this;
        }

        return $this->moonwalk(function($n) {
            return ($n->parentNode === null);
        })->current();
    }


    private function convertNodesToNode(array $nodes): Node {
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
        $node = (count($nodes) > 1) ? $document->createDocumentFragment() : null;
        foreach ($nodes as $k => &$n) {
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
