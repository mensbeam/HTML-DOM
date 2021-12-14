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


trait NonDocumentTypeChildNode {
    protected function __get_nextElementSibling(): ?Element {
        // The nextElementSibling getter steps are to return the first following sibling
        // that is an element; otherwise null.

        // PHP's DOM does this correctly already.
        $inner = $this->innerNode;
        $result = $inner->nextElementSibling;
        return ($result !== null) ? $inner->ownerDocument->getWrapperNode($result) : null;
    }

    protected function __get_previousElementSibling(): ?Element {
        // The previousElementSibling getter steps are to return the first preceding
        // sibling that is an element; otherwise null.

        // PHP's DOM does this correctly already.
        $inner = $this->innerNode;
        $result = $inner->previousElementSibling;
        return ($result !== null) ? $inner->ownerDocument->getWrapperNode($result) : null;
    }
}
