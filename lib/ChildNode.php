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
            $node = Reflection::getProtectedProperty($node, 'innerNode');
            $doc = (!$node instanceof InnerDocument) ? $node->ownerDocument : $node;

            do {
                $next = $node->parentNode;
                $wrapperNode = $doc->getWrapperNode($node);
                $result = ($filter === null) ? true : $filter($wrapperNode);

                if ($result === true) {
                    yield $wrapperNode;
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
        $node = null;
        if ($includeReferenceNode) {
            $node = $this->innerNode;
        } elseif ($this->nextSibling !== null) {
            $node = Reflection::getProtectedProperty($this->nextSibling, 'innerNode');
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
        $node = null;
        if ($includeReferenceNode) {
            $node = $this->innerNode;
        } elseif ($this->nextSibling !== null) {
            $node = Reflection::getProtectedProperty($this->previousSibling, 'innerNode');
        }

        if ($node !== null) {
            $doc = (!$node instanceof InnerDocument) ? $node->ownerDocument : $node;

            do {
                $next = $node->previousSibling;
                $wrapperNode = $doc->getWrapperNode($node);
                $result = ($filter === null) ? true : $filter($wrapperNode);

                if ($result === true) {
                    yield $wrapperNode;
                }
            } while ($node = $next);
        }
    }
}
