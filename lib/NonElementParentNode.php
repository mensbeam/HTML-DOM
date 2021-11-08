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
use MensBeam\HTML\Parser;


trait NonElementParentNode {
    public function getElementById(string $elementId): ?Element {
        $document = (!$this instanceof Element) ? $this->innerNode : $this->innerNode->ownerDocument;
        $innerElement = $this->innerNode->getElementById($elementId);
        return ($innerElement !== null) ? $document->getWrapperNode($innerElement) : null;
    }
}
