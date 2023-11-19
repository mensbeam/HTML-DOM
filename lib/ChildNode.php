<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


trait ChildNode {
    public function after(Node|string ...$nodes): void {
        // After exists in PHP DOM, but it can insert incorrect nodes because of PHP
        // DOM's incorrect (for HTML) pre-insertion validation.

        # The after(nodes) method steps are:
        #
        # 1. Let parent be this’s parent.
        $inner = $this->_innerNode;
        $parent = $this->parentNode;

        # 2. If parent is null, then return.
        if ($parent === null) {
            return;
        }

        # 3. Let viableNextSibling be this’s first following sibling not in nodes;
        #    otherwise null.
        $n = $inner;
        $viableNextSibling = null;
        while ($n = $n->nextSibling) {
            foreach ($nodes as $nodeOrString) {
                if ($nodeOrString instanceof Node && $nodeOrString->innerNode === $n) {
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
        $parent->insertBefore($node, ($viableNextSibling !== null) ? $inner->ownerDocument->getWrapperNode($viableNextSibling) : null);
    }

    public function before(Node|string ...$nodes): void {
        // Before exists in PHP DOM, but it can insert incorrect nodes because of PHP
        // DOM's incorrect (for HTML) pre-insertion validation.

        # The before(nodes) method steps are:
        #
        # 1. Let parent be this’s parent.
        $inner = $this->_innerNode;
        $parent = $this->parentNode;

        # 2. If parent is null, then return.
        if ($parent === null) {
            return;
        }

        # 3. Let viablePreviousSibling be this’s first preceding sibling not in nodes;
        #    otherwise null.
        $n = $inner;
        $viablePreviousSibling = null;
        while ($n = $n->previousSibling) {
            foreach ($nodes as $nodeOrString) {
                if ($nodeOrString instanceof Node && $nodeOrString->innerNode === $n) {
                    continue 2;
                }
            }

            $viablePreviousSibling = $n;
            break;
        }

        # 4. Let node be the result of converting nodes into a node, given nodes and
        #    this’s node document.
        $node = $this->convertNodesToNode($nodes);

        # 5. If viablePreviousSibling is null, then set it to parent’s first child;
        #    otherwise to viablePreviousSibling’s next sibling.
        $viablePreviousSibling = ($viablePreviousSibling === null) ? $parent->firstChild : $inner->ownerDocument->getWrapperNode($viablePreviousSibling->nextSibling);

        # 6. Pre-insert node into parent before viablePreviousSibling.
        $parent->insertBefore($node, $viablePreviousSibling);
    }

    public function remove(): void {
        # The remove() method steps are:
        # 1. If this’s parent is null, then return.
        if ($this->parentNode === null) {
            return;
        }

        # 2. Remove this.
        $this->parentNode->removeChild($this);
    }

    public function replaceWith(Node|string ...$nodes): void {
        // Before exists in PHP DOM, but it can insert incorrect nodes because of PHP
        // DOM's incorrect (for HTML) pre-insertion validation.

        # The replaceWith(nodes) method steps are:
        #
        # 1. Let parent be this’s parent.
        $inner = $this->_innerNode;
        $parent = $this->parentNode;

        # 2. If parent is null, then return.
        if ($parent === null) {
            return;
        }

        # 3. Let viableNextSibling be this’s first following sibling not in nodes;
        #    otherwise null.
        $n = $inner;
        $viableNextSibling = null;
        while ($n = $n->nextSibling) {
            foreach ($nodes as $nodeOrString) {
                if ($nodeOrString instanceof Node && $nodeOrString->innerNode === $n) {
                    continue 2;
                }
            }

            $viableNextSibling = $n;
            break;
        }

        # 4. Let node be the result of converting nodes into a node, given nodes and
        #    this’s node document.
        $node = $this->convertNodesToNode($nodes);

        # 5. If this’s parent is parent, replace this with node within parent.
        # Note: This could have been inserted into node.
        if ($this->parentNode === $parent) {
            $parent->replaceChild($node, $this);
        }
        # 6. Otherwise, pre-insert node into parent before viableNextSibling.
        else {
            $parent->insertBefore($node, ($viableNextSibling !== null) ? $inner->ownerDocument->getWrapperNode($viableNextSibling) : null);
        }
    }
}
