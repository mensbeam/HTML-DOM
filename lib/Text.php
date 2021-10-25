<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


class Text extends CharacterData {
    protected function __get_wholeText(): string {
        // PHP's DOM does this correctly already.
        return $this->innerNode->wholeText;
    }


    public function splitText(int $offset): Text {
        // PHP DOM mostly handles this correctly with the exception of not throwing an
        // exception when the offset is greater than the length, so let's fix that.
        if ($offset > $this->length) {
            throw new DOMException(DOMException::INDEX_SIZE_ERROR);
        }

        return $this->innerNode->ownerDocument->getWrapperNode($this->innerNode->splitText($offset));
    }


    protected function __construct(\DOMText $text) {
        parent::__construct($text);
    }
}