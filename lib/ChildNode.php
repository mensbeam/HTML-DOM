<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\Framework\MagicProperties,
    MensBeam\HTML\DOM\InnerNode\Reflection;


trait ChildNode {
    /**
     * Generator which walks backwards through the DOM from the node the method is
     * being run on. Nonstandard.
     *
     * @param ?\Closure $filter An optional callback function used to filter; if not provided the generator will
     *                          just yield every node.
     * @param bool $includeReferenceNode An optional boolean flag which if true includes the reference node ($this) in
     *                                   the iteration.
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
                    throw new Exception(Exception::RETURN_TYPE_ERROR, 'Closure', '?bool', $type);
                }

                if ($result === true) {
                    yield $node;
                }

                if ($node instanceof DocumentFragment) {
                    $host = Reflection::getProtectedProperty($node, 'host');
                    if ($host !== null) {
                        $next = $host->get();
                    }
                }
            } while ($node = $next);
        }
    }

    /**
     * Generator which walks forwards through an element's siblings. Nonstandard.
     *
     * @param ?\Closure $filter An optional callback function used to filter; if not provided the generator will
     *                          just yield every node.
     * @param bool $includeReferenceNode An optional boolean flag which if true includes the reference node ($this) in
     *                                   the iteration.
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
                    throw new Exception(Exception::RETURN_TYPE_ERROR, 'Closure', '?bool', $type);
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
     * @param ?\Closure $filter An optional callback function used to filter; if not provided the generator will
     *                          just yield every node.
     * @param bool $includeReferenceNode An optional boolean flag which if true includes the reference node ($this) in
     *                                   the iteration.
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
                    throw new Exception(Exception::RETURN_TYPE_ERROR, 'Closure', '?bool', $type);
                }

                if ($result === true) {
                    yield $node;
                }
            } while ($node = $next);
        }
    }
}
