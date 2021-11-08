<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\Inner\{
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
        if ($this instanceof DocumentFragment || (!$this instanceof DocumentFragment && !$includeReferenceNode)) {
            $node = $node->firstChild;
        }

        if ($node !== null) {
            $doc = (!$node instanceof InnerDocument) ? $node->ownerDocument : $node;

            do {
                $next = $node->nextSibling;
                $wrapperNode = $doc->getWrapperNode($node);
                $result = ($filter === null) ? Node::WALK_FILTER_ACCEPT : $filter($wrapperNode);

                switch ($result) {
                    case Node::WALK_FILTER_ACCEPT:
                        yield $wrapperNode;
                    break;
                    case Node::WALK_FILTER_ACCEPT | Node::WALK_FILTER_SKIP_CHILDREN:
                        yield $wrapperNode;
                    case Node::WALK_FILTER_REJECT | Node::WALK_FILTER_SKIP_CHILDREN:
                    continue 2;
                    case Node::WALK_FILTER_REJECT:
                    break;
                    default: return;
                }

                if ($node->parentNode !== null && $node->hasChildNodes()) {
                    yield from $node->walk($filter);
                }
            } while ($node = $next);
        }
    }


    protected function walkInner(\DOMNode $node, ?\Closure $filter = null, bool $includeReferenceNode = false): \Generator {
        if (!$node instanceof DocumentFragment && !$includeReferenceNode) {
            $node = $node->firstChild;
        }

        if ($node !== null) {
            $doc = (!$node instanceof InnerDocument) ? $node->ownerDocument : $node;

            do {
                $next = $node->nextSibling;
                $result = ($filter === null) ? Node::WALK_ACCEPT : $filter($node);

                switch ($result) {
                    case Node::WALK_ACCEPT:
                        yield $node;
                    break;
                    case Node::WALK_ACCEPT | Node::WALK_SKIP_CHILDREN:
                        yield $node;
                    case Node::WALK_REJECT | Node::WALK_SKIP_CHILDREN:
                    continue 2;
                    case Node::WALK_REJECT:
                    break;
                    default: return;
                }

                if ($node->parentNode !== null && $node->hasChildNodes()) {
                    yield from $this->walkInner($node, $filter);
                }
            } while ($node = $next);
        }
    }
}
