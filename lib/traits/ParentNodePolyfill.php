<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


if (version_compare(\PHP_VERSION, '8.0', '<')) {
    /**
     * Used for PHP7 installations to polyfill getters, setters, and methods that
     * PHP's DOM handles natively in PHP8
     */
    trait ParentNode {
        protected function __get_childElementCount(): int {
            # The childElementCount getter steps are to return the number of children of
            # this that are elements.
            $count = 0;
            foreach ($this->childNodes as $child) {
                if ($child instanceof Element) {
                    $count++;
                }
            }

            return $count;
        }

        protected function __get_firstElementChild(): Element {
            # The firstElementChild getter steps are to return the first child that is an
            # element; otherwise null.
            foreach ($this->childNodes as $child) {
                if ($child instanceof Element) {
                    return $child;
                }
            }
            return null;
        }

        protected function __get_lastElementChild(): Element {
            # The lastElementChild getter steps are to return the last child that is an
            # element; otherwise null.
            for ($i = $this->childNodes->length - 1; $i >= 0; $i--) {
                $child = $this->childNodes->item($i);
                if ($child instanceof Element) {
                    return $child;
                }
            }

            return null;
        }


        public function append(...$nodes): void {
            # The append(nodes) method steps are:
            # 1. Let node be the result of converting nodes into a node given nodes and
            #    this’s node document.
            $node = $this->convertNodesToNode($nodes);
            # 2. Append node to this.
            $this->appendChild($node);
        }

        public function prepend(...$nodes): void {
            # The prepend(nodes) method steps are:
            #
            # 1. Let node be the result of converting nodes into a node given nodes and
            #    this’s node document.
            $node = $this->convertNodesToNode($nodes);
            # 2. Pre-insert node into this before this’s first child.
            $this->insertBefore($node, $this->firstChild);
        }
    }
} else {
    trait ParentNodePolyfill {}
}
