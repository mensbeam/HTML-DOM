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
                $next = $node->parentNode;
                if ($filter === null || $filter($node) === true) {
                    yield $node;
                }

                // If node hasn't been removed and is an instance of DocumentFragment then set
                // the node to its host if it isn't null.
                if ($node instanceof DocumentFragment) {
                    $host = $node->host;
                    if ($host !== null) {
                        $next = $host;
                    }
                }
            } while ($node = $next);
        }
    }
}
