<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\InnerNode\{
    Document as InnerDocument,
    Reflection
};


trait ParentNode {
    /**
     * Generator which walks down the DOM from the node the method is being run on.
     * Non-standard.
     *
     * @param ?\Closure $filter An optional callback function used to filter; if not provided the generator will
     *                          just yield every node.
     * @param bool $includeReferenceNode An optional boolean flag which if true includes the reference node ($this) in
     *                                   the iteration.
     */
    public function walk(?\Closure $filter = null, bool $includeReferenceNode = false): \Generator {
        $node = (!$node instanceof DocumentFragment) ? $this->getInnerNode($node) : null;
        if (!$includeReferenceNode) {
            $node = $node->firstChild;
        }

        if ($node !== null) {
            $doc = (!$node instanceof InnerDocument) ? $node->ownerDocument : $node;

            do {
                $next = $node->nextSibling;
                $wrapperNode = $doc->getWrapperNode($node);
                $result = ($filter === null) ? true : $filter($wrapperNode);

                if ($result === true) {
                    yield $wrapperNode;
                }

                // If the filter returns true (accept) or false (skip) and the node wasn't
                // removed in the filter iterate through the children
                if ($result !== null && $node->parentNode !== null && $node->hasChildNodes()) {
                    yield from $node->walk($filter);
                }
            } while ($node = $next);
        }
    }
}
