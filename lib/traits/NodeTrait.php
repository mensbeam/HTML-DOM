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
        throw new DOMException(DOMException::NOT_SUPPORTED, __METHOD__ . ' is meant for XML and buggy; use Document::saveHTML or cast to a string');
    }

    // Disable C14NFile
    public function C14NFile($uri, $exclusive = null, $with_comments = null, ?array $xpath = null, ?array $ns_prefixes = null): bool {
        throw new DOMException(DOMException::NOT_SUPPORTED, __METHOD__ . ' is meant for XML and buggy; use Document::saveHTMLFile');
    }

    public function compareDocumentPosition(\DOMNode $other): int {
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

    // Disable getLineNo
    public function getLineNo(): int {
        throw new DOMException(DOMException::NOT_SUPPORTED, __METHOD__ . ' is not in the standard, is buggy, and useless');
    }

    public function getRootNode(): ?\DOMNode {
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
