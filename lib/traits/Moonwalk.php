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
}
