<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


/** @property \DOMText $_innerNode */
class Text extends CharacterData {
    protected function __get_wholeText(): string {
        // PHP's DOM does this correctly already.
        return $this->_innerNode->wholeText;
    }


    public function splitText(int $offset): Text {
        // PHP DOM mostly handles this correctly with the exception of not throwing an
        // exception when the offset is greater than the length, so let's fix that.
        // DEVIATION?: All browsers error when supplying negative numbers here, so let's check for that, too.
        if ($offset < 0 || $offset > $this->length) {
            # 2. If offset is greater than length, then throw an "IndexSizeError" DOMException.
            // DEVIATION?: WebIDL standard
            // (https://webidl.spec.whatwg.org/#idl-DOMException-error-names) says
            // IndexSizeError is deprecated and to use RangeError instead which isn't a
            // DOMException but specified to be an analog of ECMAScript's built-in errors.
            // Because of this we're going to use OutOfBoundsException which is PHP's
            // equivalent.
            throw new OutOfBoundsException(sprintf('Offset %s is outside of Text node\'s range of %s to %s', $offset, 0, $this->length));
        }

        /** @var \MensBeam\HTML\DOM\Inner\Document */
        $ownerDocument = $this->_innerNode->ownerDocument;
        return $ownerDocument->getWrapperNode($this->_innerNode->splitText($offset));
    }


    protected function __construct(\DOMText $text) {
        parent::__construct($text);
    }
}