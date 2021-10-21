<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\InnerNode\Document as InnerDocument;


class Document extends Node {
    use ParentNode;

    public function __construct() {
        parent::__construct(new InnerDocument($this));
    }


    public function createDocumentFragment(): DocumentFragment {
        return $this->innerNode->getWrapperNode($this->innerNode->createDocumentFragment());
    }

    public function createElement(string $localName): Element {
        return $this->innerNode->getWrapperNode($this->innerNode->createElement($localName));
    }
}