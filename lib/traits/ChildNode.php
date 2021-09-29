<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


trait ChildNode {
    use Node;


    public function appendChild($node) {
        throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
    }

    public function insertBefore($node, $child = null) {
        throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
    }

    public function removeChild($child) {
        throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
    }

    public function replaceChild($node, $child) {
        throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
    }
}
