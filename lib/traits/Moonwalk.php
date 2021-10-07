<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


trait Moonwalk {
    /**
     * Generator which walks backwards through the DOM from the node the method is
     * being run on. Nonstandard.
     *
     * @param ?\Closure $filter - An optional callback function used to filter; if not provided the generator will
     *                            just yield every node.
     */
    public function moonwalk(?\Closure $filter = null): \Generator {
        $node = $this->parentNode;
        if ($node !== null) {
            do {
                while (true) {
                    $next = $node->parentNode;
                    $prevSibling = $node->previousSibling;
                    $nextSibling = $node->nextSibling;
                    if ($filter === null || $filter($node) === true) {
                        yield $node;
                    }

                    // If the node was replaced mid-loop then make node be the element that it was
                    // replaced with by determining the previous node's position.
                    if (!$node instanceof Document && $node->parentNode === null) {
                        if ($prevSibling === null) {
                            $node = $next->firstChild;
                        } elseif ($nextSibling === null) {
                            $node = $next->lastChild;
                        } else {
                            $node = $prevSibling->nextSibling;
                        }
                    }

                    // If node is an instance of DocumentFragment then set the node to its host if
                    // it isn't null.
                    if ($node instanceof DocumentFragment) {
                        $host = $node->host;
                        if ($host !== null) {
                            $node = $host;
                        }
                    }

                    break;
                }
            } while ($node = $next);
        }
    }
}
