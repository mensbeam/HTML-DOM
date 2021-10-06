<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


trait Walk {
    /**
     * Generator which walks down the DOM from the node the method is being run on.
     * Nonstandard.
     *
     * @param ?\Closure $filter - An optional callback function used to filter; if not provided the generator will
     *                            just yield every node.
     */
    public function walk(?\Closure $filter = null): \Generator {
        $node = $this->firstChild;
        if ($node !== null) {
            do {
                $next = $node->nextSibling;
                $parent = $node->parentNode;
                $prev = $node->previousSibling;
                if ($filter === null || $filter($node) === true) {
                    yield $node;
                }

                // If the node was replaced mid-loop then make node be the element that it was
                // replaced with by determining the previous node's position.
                if ($node->parentNode === null) {
                    $node = $prev->nextSibling ?? $parent->firstChild;
                }

                if ($node instanceof HTMLTemplateElement) {
                    $node = $node->content;
                }

                if ($node->hasChildNodes()) {
                    yield from $node->walk($filter);
                }
            } while ($node = $next);
        }
    }

    /**
     * Generator which just walks through a node's child nodes. Nonstandard.
     *
     * @param ?\Closure $filter - An optional callback function used to filter; if not provided the generator will
     *                            just yield every node.
     * @param bool $backwards - An optional setting that if true makes the generator instead walk backwards
     *                          through the child nodes.
     */
    public function walkShallow(?\Closure $filter = null, bool $backwards = false): \Generator {
        $node = (!$this instanceof TemplateElement) ? $this : $this->content;

        if (!$backwards) {
            $node = $node->firstChild;
            if ($node !== null) {
                do {
                    $next = $node->nextSibling;
                    if ($filter === null || $filter($node) === true) {
                        yield $node;
                    }
                } while ($node = $next);
            }
        } else {
            $node = $node->lastChild;
            if ($node !== null) {
                do {
                    $next = $node->previousSibling;
                    if ($filter === null || $filter($node) === true) {
                        yield $node;
                    }
                } while ($node = $next);
            }
        }
    }
}
