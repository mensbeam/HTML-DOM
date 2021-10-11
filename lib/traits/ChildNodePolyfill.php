<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


// @codeCoverageIgnoreStart
if (version_compare(\PHP_VERSION, '8.0', '<')) {
    # 4.2.7. Mixin NonDocumentTypeChildNode
    // DEVIATION: Since we do not extend \DOMDocumentType there's no need to have
    // any differentiation between ChildNode and NonDocumentTypeChildNode
    trait ChildNodePolyfill {
        protected function __get_nextElementSibling(): Element {
            # The nextElementSibling getter steps are to return the first following sibling
            # that is an element; otherwise null.
            if ($this->parentNode !== null) {
                $start = false;
                foreach ($this->parentNode->childNodes as $child) {
                    if (!$start) {
                        if ($child->isSameNode($this)) {
                            $start = true;
                        }

                        continue;
                    }

                    if (!$child instanceof Element) {
                        continue;
                    }

                    return $child;
                }
            }

            return null;
        }

        protected function __get_previousElementSibling(): Element {
            # The previousElementSibling getter steps are to return the first preceding
            # sibling that is an element; otherwise null.
            if ($this->parentNode !== null) {
                foreach ($this->parentNode->childNodes as $child) {
                    if ($child->isSameNode($this)) {
                        return null;
                    }

                    if (!$child instanceof Element) {
                        continue;
                    }

                    return $child;
                }
            }

            return null;
        }
    }
} else {
// @codeCoverageIgnoreEnd
    trait ChildNodePolyfill {}
}
