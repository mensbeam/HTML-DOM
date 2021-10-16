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
        // Node|string typing for the nodes that it should, so type checking will
        // need to be done manually.
        foreach ($nodes as $node) {
            if (!$node instanceof Node && !is_string($node)) {
                $type = gettype($node);
                if ($type === 'object') {
                    $type = get_class($node);
                }
                throw new Exception(Exception::ARGUMENT_TYPE_ERROR, 1, 'nodes', 'Node|string', $type);
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
                if ($nodeOrString instanceof Node && $nodeOrString === $n) {
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
        // PHP's declaration for \DOMCharacterData::before doesn't include the
        // Node|string typing for the nodes that it should, so type checking will
        // need to be done manually.
        foreach ($nodes as $node) {
            if (!$node instanceof Node && !is_string($node)) {
                $type = gettype($node);
                if ($type === 'object') {
                    $type = get_class($node);
                }
                throw new Exception(Exception::ARGUMENT_TYPE_ERROR, 1, 'nodes', 'Node|string', $type);
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

        # 3. Let viablePreviousSibling be this’s first preceding sibling not in nodes;
        #    otherwise null.
        $n = $this;
        $viablePreviousSibling = null;
        while ($n = $n->previousSibling) {
            foreach ($nodes as $nodeOrString) {
                if ($nodeOrString instanceof Node && $nodeOrString === $n) {
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
        $viablePreviousSibling = ($viablePreviousSibling === null) ? $parent->firstChild : $viablePreviousSibling->nextSibling;

        # 6. Pre-insert node into parent before viablePreviousSibling.
        $parent->insertBefore($node, $viablePreviousSibling);
    }

    /**
     * Generator which walks backwards through the DOM from the node the method is
     * being run on. Nonstandard.
     *
     * @param ?\Closure $filter - An optional callback function used to filter; if not provided the generator will
     *                            just yield every node.
     * @param bool $includeReferenceNode - An optional boolean flag which if true includes the reference node ($this) in
     *                                     the iteration.
     */
    public function moonwalk(?\Closure $filter = null, bool $includeReferenceNode = false): \Generator {
        $node = $this->parentNode;
        if ($node !== null) {
            do {
                $next = $node->parentNode;
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

                if ($node instanceof DocumentFragment) {
                    $host = $node->host;
                    if ($host !== null) {
                        $next = $host;
                    }
                }
            } while ($node = $next);
        }
    }

    public function replaceWith(...$nodes): void {
        // Before exists in PHP DOM, but it can insert incorrect nodes because of PHP
        // DOM's incorrect (for HTML) pre-insertion validation.
        // PHP's declaration for \DOMCharacterData::replaceWith doesn't include the
        // Node|string typing for the nodes that it should, so type checking will
        // need to be done manually.
        foreach ($nodes as $node) {
            if (!$node instanceof Node && !is_string($node)) {
                $type = gettype($node);
                if ($type === 'object') {
                    $type = get_class($node);
                }
                throw new Exception(Exception::ARGUMENT_TYPE_ERROR, 1, 'nodes', 'Node|string', $type);
            }
        }

        # The replaceWith(nodes) method steps are:
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
                if ($nodeOrString instanceof Node && $nodeOrString === $n) {
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
            $parent->insertBefore($node, $viableNextSibling);
        }
    }

    /**
     * Generator which walks forwards through an element's siblings. Nonstandard.
     *
     * @param ?\Closure $filter - An optional callback function used to filter; if not provided the generator will
     *                            just yield every node.
     * @param bool $includeReferenceNode - An optional boolean flag which if true includes the reference node ($this) in
     *                                     the iteration.
     */
    public function walkFollowing(?\Closure $filter = null, bool $includeReferenceNode = false): \Generator {
        $node = ($includeReferenceNode) ? $this : $this->nextSibling;
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
            } while ($node = $next);
        }
    }

    /**
     * Generator which walks backwards through an element's siblings. Nonstandard.
     *
     * @param ?\Closure $filter - An optional callback function used to filter; if not provided the generator will
     *                            just yield every node.
     * @param bool $includeReferenceNode - An optional boolean flag which if true includes the reference node ($this) in
     *                                     the iteration.
     */
    public function walkPreceding(?\Closure $filter = null, bool $includeReferenceNode = false): \Generator {
        $node = ($includeReferenceNode) ? $this : $this->previousSibling;
        if ($node !== null) {
            do {
                $next = $node->previousSibling;
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
            } while ($node = $next);
        }
    }
}
