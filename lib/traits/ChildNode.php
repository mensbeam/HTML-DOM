<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


# 4.2.8. Mixin ChildNode
trait ChildNode {
    public function after(...$nodes): void {
        // After exists in PHP DOM, but it can insert incorrect nodes because of PHP
        // DOM's incorrect (for HTML) pre-insertion validation.
        // PHP's declaration for \DOMCharacterData::after doesn't include the
        // \DOMNode|string typing for the nodes that it should, so type checking will
        // need to be done manually.
        foreach ($nodes as $node) {
            if (!$node instanceof \DOMNode && !is_string($node)) {
                $type = gettype($node);
                if ($type === 'object') {
                    $type = get_class($node);
                }
                throw new Exception(Exception::ARGUMENT_TYPE_ERROR, 1, 'nodes', '\DOMNode|string', $type);
            }
        }

        # The after(nodes) method steps are:
        #
        # 1. Let parent be this’s parent.
        $parent = $this->parentNode;

        # 2. If parent is null, then return.
        if ($parent === null) {
            return;
        }

        # 3. Let viableNextSibling be this’s first following sibling not in nodes;
        #    otherwise null.
        $n = $this;
        $viableNextSibling = null;
        while ($n = $n->nextSibling) {
            foreach ($nodes as $nodeOrString) {
                if ($nodeOrString instanceof \DOMNode && $nodeOrString->isSameNode($n)) {
                    continue 2;
                }
            }

            $viableNextSibling = $n;
            break;
        }

        # 4. Let node be the result of converting nodes into a node, given nodes and this’s
        #    node document.
        $node = $this->convertNodesToNode($nodes);

        # 5. Pre-insert node into parent before viableNextSibling.
        $parent->insertBefore($node, $viableNextSibling);
    }

    public function before(...$nodes): void {
        // Before exists in PHP DOM, but it can insert incorrect nodes because of PHP
        // DOM's incorrect (for HTML) pre-insertion validation.
        // PHP's declaration for \DOMCharacterData::after doesn't include the
        // \DOMNode|string typing for the nodes that it should, so type checking will
        // need to be done manually.
        foreach ($nodes as $node) {
            if (!$node instanceof \DOMNode && !is_string($node)) {
                $type = gettype($node);
                if ($type === 'object') {
                    $type = get_class($node);
                }
                throw new Exception(Exception::ARGUMENT_TYPE_ERROR, 1, 'nodes', '\DOMNode|string', $type);
            }
        }

        # The before(nodes) method steps are:
        #
        # 1. Let parent be this’s parent.
        $parent = $this->parentNode;

        # 2. If parent is null, then return.
        if ($parent === null) {
            return;
        }

        # 3. Let viablePreviousSibling be this’s first preceding sibling not in nodes; otherwise null.
        $n = $this;
        $viablePreviousSibling = null;
        while ($n = $n->previousSibling) {
            foreach ($nodes as $nodeOrString) {
                if ($nodeOrString instanceof \DOMNode && $nodeOrString->isSameNode($n)) {
                    continue 2;
                }
            }

            $viablePreviousSibling = $n;
            break;
        }

        # 4. Let node be the result of converting nodes into a node, given nodes and this’s node document.
        $node = $this->convertNodesToNode($nodes);

        # 5. If viablePreviousSibling is null, then set it to parent’s first child; otherwise to viablePreviousSibling’s next sibling.
        $viablePreviousSibling = ($viablePreviousSibling === null) ? $parent->firstChild : $viablePreviousSibling->nextSibling;

        # 6. Pre-insert node into parent before viablePreviousSibling.
        $parent->insertBefore($node, $viablePreviousSibling);
    }
}
