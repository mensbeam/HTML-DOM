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

                    // If node is an instance of DocumentFragment then it might be the content
                    // fragment of a template element, so iterate through all template elements
                    // stored in the element map and see if node is the fragment of one of the
                    // templates; if it is change node to the template element and reprocess. Magic!
                    // Can walk backwards THROUGH templates!
                    if ($node instanceof DocumentFragment) {
                        foreach (ElementMap::getIterator() as $element) {
                            if ($element->ownerDocument->isSameNode($node->ownerDocument) && $element instanceof TemplateElement && $element->content->isSameNode($node)) {
                                $node = $element;
                                continue;
                            }
                        }
                    }

                    break;
                }
            } while ($node = $next);
        }
    }
}
