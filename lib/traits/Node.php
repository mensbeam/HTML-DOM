<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


// Extensions to PHP's DOM cannot inherit from an extended Node parent, so a
// trait is the next best thing...
trait Node {
    // Disable C14N
    public function C14N($exclusive = null, $with_comments = null, ?array $xpath = null, ?array $ns_prefixes = null): bool {
        throw new DOMException(DOMException::NOT_SUPPORTED, __CLASS__ . ' is meant for XML and buggy; use Document::saveHTML or cast to a string');
    }

    // Disable C14NFile
    public function C14NFile($uri, $exclusive = null, $with_comments = null, ?array $xpath = null, ?array $ns_prefixes = null): bool {
        throw new DOMException(DOMException::NOT_SUPPORTED, __CLASS__ . ' is meant for XML and buggy; use Document::saveHTMLFile');
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
            if ($n->parentNode === null) {
                return true;
            }
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
