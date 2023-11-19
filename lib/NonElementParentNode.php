<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


trait NonElementParentNode {
    public function getElementById(string $elementId): ?Element {
        $document = $this->getInnerDocument();
        $innerElement = $this->_innerNode->getElementById($elementId);
        return ($innerElement !== null) ? $document->getWrapperNode($innerElement) : null;
    }
}
