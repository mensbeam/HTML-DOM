<?php
/** @license MIT
 * Copyright 2017 , Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;

class HTMLElement extends Element {
    protected function __get_accessKey(): string {
        # The accessKey IDL attribute must reflect the accesskey content attribute.
        return $this->getAttribute('accesskey');
    }

    protected function __set_accessKey(string $value) {
        return $this->setAttribute('accesskey', $value);
    }

    protected function __get_contentEditable(): string {
        # The contentEditable IDL attribute, on getting, must return the string "true"
        # if the content attribute is set to the true state, "false" if the content
        # attribute is set to the false state, and "inherit" otherwise.
        $result = $this->getAttribute('contenteditable');
        switch ($value) {
            case 'false':
            case 'true':
                return $result;
            default:
                return 'inherit';
        }
    }

    protected function __set_contentEditable(string $value) {
        # On setting, if the new value is an ASCII case-insensitive match for the
        # string "inherit" then the content attribute must be removed, if the new value
        # is an ASCII case-insensitive match for the string "true" then the content
        # attribute must be set to the string "true", if the new value is an ASCII
        # case-insensitive match for the string "false" then the content attribute must
        # be set to the string "false", and otherwise the attribute setter must throw a
        # "SyntaxError" DOMException.
        switch ($value) {
            case 'inherit'
                $this->removeAttribute('contenteditable');
            case 'false':
            case 'true':
                return $this->setAttribute('contenteditable', $value);
            default:
                throw new DOMException(DOMException::SYNTAX_ERROR);
        }
    }

    protected function __get_isContentEditable(): bool {
        # The isContentEditable IDL attribute, on getting, must return true if the
        # element is either an editing host or editable, and false otherwise.
        #
        # An editing host is either an HTML element with its contenteditable attribute
        # in the true state, or a child HTML element of a Document whose design mode
        # enabled is true.
        #
        # Something is editable if it is a node; it is not an editing host; it does
        # not have a contenteditable attribute set to the false state; its parent is an
        # editing host or editable; and either it is an HTML element, or it is an svg or
        # math element, or it is not an Element and its parent is an HTML element.
        $contentEditable = $this->__get_contentEditable();
        $designMode = ($this->ownerDocument->designMode === 'on');
        if ($contentEditable === 'true' || $designMode) {
            return true;
        } elseif ($contentEditable !== 'false') {
            // If the parent can be either an editing host or editable then all is needed
            // is to see if there's an ancestor that's an editing host. Just seems absurd
            // to word the specification like that. Since isContentEditable is a property
            // of HTMLElement there's no need to check if it's an HTML element, svg, or
            // non-element child of foreign content. There is also no need to check for
            // design mode enabled on the document because it's checked above.
            if ($this->moonwalk(function($n) {
                if ($n instanceof HTMLElement && $n->contentEditable === 'true') {
                    return true;
                }
            })->current() !== null) {
                return true;
            }
        }

        return false;
    }
}
