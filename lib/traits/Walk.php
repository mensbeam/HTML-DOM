<?php
/** @license MIT
 * Copyright 2017 , Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;

trait Walk {
    /** Generator which walks down the DOM. Nonstandard. */
    public function walk(?\Closure $filter = null): \Generator {
        return $this->walkGenerator($this, $filter);
    }

    private function walkGenerator(\DOMNode $node, ?\Closure $filter = null) {
        if ($filter === null || $filter($node) === true) {
            yield $node;
        }

        if ($node instanceof TemplateElement) {
            $node = $node->content;
        }

        if ($node->hasChildNodes()) {
            $children = $node->childNodes;
            foreach ($children as $c) {
                yield from $this->walkGenerator($c, $filter);
            }
        }
    }
}
