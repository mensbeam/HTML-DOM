<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\Framework\MagicProperties;


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
        $node = ($includeReferenceNode && !$this instanceof DocumentFragment) ? $this : $this->firstChild;

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
                    throw new DOMException(DOMException::RETURN_TYPE_ERROR, 'Closure', '?bool', $type);
                }

                if ($result === true) {
                    yield $node;
                }

                // If the filter returns true (accept) or false (skip) and the node wasn't
                // removed in the filter iterate through the children
                if ($result !== null && $node->parentNode !== null) {
                    if ($node instanceof HTMLTemplateElement) {
                        $node = $node->content;
                    }

                    if ($node->hasChildNodes()) {
                        yield from $node->walk($filter);
                    }
                }
            } while ($node = $next);
        }
    }
}
