<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


class HTMLElement extends Element {
    use HTMLOrSVGElement;

    protected function __get_accessKey(): string {
        # The accessKey IDL attribute must reflect the accesskey content attribute.
        return $this->getAttribute('accesskey') ?? '';
    }

    protected function __set_accessKey(string $value): void {
        $this->setAttribute('accesskey', $value);
    }

    protected function __get_autocapitalize(): string {
        # The autocapitalize getter steps are to:
        #
        # 1. Let state be the own autocapitalization hint of this.
        $state = $this->autoCapitalizationHint($this->_innerNode);

        # 2. If state is default, then return the empty string.
        # 3. If state is none, then return "none".
        # 4. If state is sentences, then return "sentences".
        # 5. Return the keyword value corresponding to state.
        // Switch below will handle all of these steps.

        # The autocapitalize attribute is an enumerated attribute whose states are the
        # possible autocapitalization hints. The autocapitalization hint specified by
        # the attribute's state combines with other considerations to form the used
        # autocapitalization hint, which informs the behavior of the user agent. The
        # keywords for this attribute and their state mappings are as follows:
        switch ($state) {
            case 'default':
                return '';
            case 'none':
            case 'sentences':
            case 'words':
            case 'characters':
                return $state;
            case 'off':
                return 'none';
            case 'on':
            # The invalid value default is the sentences state.
            default: return 'sentences';
        }
    }

    protected function __set_autocapitalize(string $value): void {
        # The autocapitalize setter steps are to set the autocapitalize content
        # attribute to the given value.
        $this->setAttribute('autocapitalize', $value);
    }

    protected function __get_contentEditable(): string {
        # The contenteditable content attribute is an enumerated attribute whose
        # keywords are the empty string, true, and false. The empty string and the true
        # keyword map to the true state. The false keyword maps to the false state. In
        # addition, there is a third state, the inherit state, which is the missing
        # value default and the invalid value default.

        # The true state indicates that the element is editable. The inherit state
        # indicates that the element is editable if its parent is. The false state
        # indicates that the element is not editable.

        # The contentEditable IDL attribute, on getting, must return the string "true"
        # if the content attribute is set to the true state, "false" if the content
        # attribute is set to the false state, and "inherit" otherwise.

        # On setting, if the new value is an ASCII case-insensitive match for the string
        # "inherit" then the content attribute must be removed, if the new value is an
        # ASCII case-insensitive match for the string "true" then the content attribute
        # must be set to the string "true", if the new value is an ASCII
        # case-insensitive match for the string "false" then the content attribute must
        # be set to the string "false", and otherwise the attribute setter must throw a
        # "SyntaxError" DOMException.

        $value = strtolower($this->getAttribute('contenteditable') ?? '');
        return ($value === 'true' || $value === 'false') ? $value : 'inherit';
    }

    protected function __set_contentEditable(string $value): void {
        # On setting, if the new value is an ASCII case-insensitive match for the string
        # "inherit" then the content attribute must be removed, if the new value is an
        # ASCII case-insensitive match for the string "true" then the content attribute
        # must be set to the string "true", if the new value is an ASCII
        # case-insensitive match for the string "false" then the content attribute must
        # be set to the string "false", and otherwise the attribute setter must throw a
        # "SyntaxError" DOMException.
        $value = strtolower($value);
        switch ($value) {
            case 'inherit':
                $this->removeAttribute('contenteditable');
            break;
            case 'true':
            case 'false':
                $this->setAttribute('contenteditable', $value);
            break;
            default: throw new DOMException(DOMException::SYNTAX_ERROR);
        }
    }

    protected function __get_dir(): string {
        # The dir IDL attribute on an element must reflect the dir content attribute of
        # that element, limited to only known values.

        # If a reflecting IDL attribute is a DOMString attribute whose content attribute
        # is an enumerated attribute, and the IDL attribute is limited to only known
        # values, then, on getting, the IDL attribute must return the keyword value
        # associated with the state the attribute is in, if any, or the empty string if
        # the attribute is in a state that has no associated keyword value or if the
        # attribute is not in a defined state (e.g. the attribute is missing and there
        # is no missing value default). If there are multiple keyword values for the
        # state, then return the conforming one. If there are multiple conforming
        # keyword values, then one will be designated the canonical keyword; choose that
        # one.

        $value = $this->getAttribute('dir');
        return (in_array($value, [ 'auto', 'ltr', 'rtl' ])) ? $value : '';
    }

    protected function __set_dir(string $value): void {
        # On setting, the content attribute must be set to the specified new value.
        $this->setAttribute('dir', $value);
    }

    protected function __get_draggable(): bool {
        # The draggable IDL attribute, whose value depends on the content attribute's in
        # the way described below, controls whether or not the element is draggable.
        # Generally, only text selections are draggable, but elements whose draggable
        # IDL attribute is true become draggable as well.

        # If an element's draggable content attribute has the state true, the draggable
        # IDL attribute must return true.

        # Otherwise, if the element's draggable content attribute has the state false,
        # the draggable IDL attribute must return false.

        # Otherwise, the element's draggable content attribute has the state auto. If
        # the element is an img element, an object element that represents an image, or
        # an a element with an href content attribute, the draggable IDL attribute must
        # return true; otherwise, the draggable IDL attribute must return false.

        $value = $this->getAttribute('draggable');
        $value = ($value === 'true' || $value === 'false') ? $value : 'auto';
        if ($value === 'true') {
            return true;
        } elseif ($value === 'false') {
            return false;
        }

        $tagName = $this->tagName;
        if ($tagName === 'img' || $this->hasAttribute('href')) {
            return true;
        }
        // Without actually being able to read the image in question it's impossible to
        // completely tell if an object element is representing an image. What we're
        // going to do here is check for a type attribute with a mimetype associated
        // with a known web image type, and, failing that, check the file extension of
        // the data attribute.
        elseif ($tagName === 'object') {
            $type = $this->getAttribute('type');
            if ($type !== null && in_array($type, [ 'image/apng', 'image/avif', 'image/gif', 'image/jpeg', 'image/png', 'image/svg+xml', 'image/webp' ])) {
                return true;
            }

            $data = $this->getAttribute('data');
            if ($data !== null && str_contains(needle: '.', haystack: $data) && in_array(strtolower(substr(strrchr($data, '.'), 1)), [ 'apng', 'avif', 'gif', 'jpeg', 'jpg', 'png', 'svg', 'webp' ])) {
                return true;
            }
        }

        return false;
    }

    protected function __set_draggable(bool $value): void {
        # If the draggable IDL attribute is set to the value false, the draggable
        # content attribute must be set to the literal value "false". If the draggable
        # IDL attribute is set to the value true, the draggable content attribute must
        # be set to the literal value "true".
        $this->setAttribute('draggable', ($value) ? 'true' : 'false');
    }

    protected function __get_enterKeyHint(): string {
        # The enterKeyHint IDL attribute must reflect the enterkeyhint content
        # attribute, limited to only known values.

        # If a reflecting IDL attribute is a DOMString attribute whose content attribute
        # is an enumerated attribute, and the IDL attribute is limited to only known
        # values, then, on getting, the IDL attribute must return the keyword value
        # associated with the state the attribute is in, if any, or the empty string if
        # the attribute is in a state that has no associated keyword value or if the
        # attribute is not in a defined state (e.g. the attribute is missing and there
        # is no missing value default). If there are multiple keyword values for the
        # state, then return the conforming one. If there are multiple conforming
        # keyword values, then one will be designated the canonical keyword; choose that
        # one.

        $value = $this->getAttribute('enterkeyhint');
        return (in_array($value, [ 'done', 'enter', 'go', 'next', 'previous', 'search', 'send' ])) ? $value : '';
    }

    protected function __set_enterKeyHint(string $value): void {
        # On setting, the content attribute must be set to the specified new value.
        $this->setAttribute('enterkeyhint', $value);
    }

    protected function __get_hidden(): bool {
        # The hidden getter steps are to return true if this's visibility state is
        # "hidden", otherwise false.
        // There's no visibility state in this implementation because there's nothing to
        // render. The only way for the visibility state to be off is if the element is
        // hidden via the hidden attribute.

        if ($this->hasAttribute('hidden')) {
            return true;
        }

        $n = $this->_innerNode;
        $doc = $n->ownerDocument;
        while ($n = $n->parentNode) {
            if ($doc->getWrapperNode($n) instanceof HTMLElement && $n->hasAttribute('hidden')) {
                return true;
            }
        }

        return false;
    }

    protected function __set_hidden(bool $value): void {
        # The autofocus IDL attribute must reflect the content attribute of the same
        # name.

        # If a reflecting IDL attribute is a boolean attribute, then on getting the IDL
        # attribute must return true if the content attribute is set, and false if it is
        # absent. On setting, the content attribute must be removed if the IDL attribute
        # is set to false, and must be set to the empty string if the IDL attribute is
        # set to true. (This corresponds to the rules for boolean content attributes.)

        if ($value) {
            $this->setAttribute('hidden', '');
        } else {
            $this->removeAttribute('hidden');
        }
    }

    protected function __get_innerText(): ?string {
        # The innerText and outerText getter steps are:
        # 1. If this is not being rendered or if the user agent is a non-CSS user agent,
        #    then return this's descendant text content.
        // This is a non-CSS user agent. Nothing else to do here.
        return $this->__get_textContent();
    }

    protected function __set_innerText(string $value): void {
        # The innerText setter steps are:
        # 1. Let fragment be the rendered text fragment for the given value given this's node
        #    document.
        $fragment = $this->getRenderedTextFragment($value);

        # 2. Replace all with fragment within this.
        $innerNode = $this->_innerNode;
        $children = $innerNode->childNodes;
        while ($innerNode->hasChildNodes()) {
            $innerNode->removeChild($innerNode->firstChild);
        }

        // Check for child nodes before appending to prevent a stupid warning.
        if ($fragment->hasChildNodes()) {
            $innerNode->appendChild($fragment);
        }
    }

    protected function __get_inputMode(): string {
        # The inputMode IDL attribute must reflect the inputmode content attribute,
        # limited to only known values.

        # If a reflecting IDL attribute is a DOMString attribute whose content attribute
        # is an enumerated attribute, and the IDL attribute is limited to only known
        # values, then, on getting, the IDL attribute must return the keyword value
        # associated with the state the attribute is in, if any, or the empty string if
        # the attribute is in a state that has no associated keyword value or if the
        # attribute is not in a defined state (e.g. the attribute is missing and there
        # is no missing value default). If there are multiple keyword values for the
        # state, then return the conforming one. If there are multiple conforming
        # keyword values, then one will be designated the canonical keyword; choose that
        # one.

        $value = $this->getAttribute('inputmode');
        return (in_array($value, [ 'decimal', 'email', 'none', 'numeric', 'search', 'tel', 'text', 'url' ])) ? $value : '';
    }

    protected function __set_inputMode(string $value): void {
        # On setting, the content attribute must be set to the specified new value.
        $this->setAttribute('inputmode', $value);
    }

    protected function __get_isContentEditable(): bool {
        # The isContentEditable IDL attribute, on getting, must return true if the
        # element is either an editing host or editable, and false otherwise.

        # An editing host is either an HTML element with its contenteditable attribute
        # in the true state, or a child HTML element of a Document whose design mode
        # enabled is true.

        # Something is editable if it is a node; it is not an editing host; it does not
        # have a contenteditable attribute set to the false state; its parent is an
        # editing host or editable; and either it is an HTML element, or it is an svg or
        # math element, or it is not an Element and its parent is an HTML element.

        $doc = ($this instanceof Document) ? $this : $this->ownerDocument;
        if ($doc->designMode === 'on') {
            return true;
        }

        $value = $this->getAttribute('contenteditable');
        if ($value !== null) {
            $value = strtolower($value);
            if ($value === 'true') {
                return true;
            } elseif ($value === 'false') {
                return false;
            }
        }

        $n = $this->_innerNode;
        while ($n = $n->parentNode) {
            if ($n instanceof \DOMElement) {
                if ($n->getAttribute('contenteditable') === 'true' && $n->ownerDocument->getWrapperNode($n) instanceof HTMLElement) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function __get_lang(): string {
        # The accessKey IDL attribute must reflect the title content attribute in no namespace.
        return $this->getAttribute('lang') ?? '';
    }

    protected function __set_lang(string $value): void {
        $this->setAttribute('lang', $value);
    }

    protected function __get_outerText(): ?string {
        # The innerText and outerText getter steps are:
        # 1. If this is not being rendered or if the user agent is a non-CSS user agent,
        #    then return this's descendant text content.
        // This is a non-CSS user agent. Nothing else to do here.
        return $this->__get_textContent();
    }

    protected function __set_outerText(string $value): void {
        # The outerText setter steps are:
        # 1. If this's parent is null, then throw a "NoModificationAllowedError"
        # DOMException.
        $innerNode = $this->_innerNode;
        if ($this->parentNode === null) {
            throw new DOMException(DOMException::NO_MODIFICATION_ALLOWED);
        }

        # 2. Let next be this's next sibling.
        $next = $innerNode->nextSibling;

        # 3. Let previous be this's previous sibling.
        $previous = $innerNode->previousSibling;

        # 4. Let fragment be the rendered text fragment for the given value given this's node
        #    document.
        $fragment = $this->getRenderedTextFragment($value);

        # 5. Replace this with fragment within this's parent.
        // Check for child nodes before appending to prevent a stupid warning.
        if ($fragment->hasChildNodes()) {
            $innerNode->parentNode->replaceChild($fragment, $innerNode);
        } else {
            $innerNode->parentNode->removeChild($innerNode);
        }

        # 6. If next is non-null and next's previous sibling is a Text node, then merge
        #    with the next text node given next's previous sibling.
        if ($next !== null && $next->previousSibling instanceof \DOMText) {
            # To merge with the next text node given a Text node node:
            # 1. Let next be node's next sibling.
            # 2. If next is not a Text node, then return.
            // Already checked for

            # 3. Replace data with node, node's data's length, 0, and next's data.
            $next->previousSibling->data .= $next->data;

            # 4. If next's parent is non-null, then remove next.
            // DEVIATION: There are no mutation events in this implementation, so there's no
            // reason to check for a parent here.
            $next->parentNode->removeChild($next);
        }

        # 7. If previous is a Text node, then merge with the next text node given previous.
        if ($previous instanceof \DOMText) {
            # To merge with the next text node given a Text node node:
            # 1. Let next be node's next sibling.
            $next = $previous->nextSibling;

            # 2. If next is not a Text node, then return.
            if ($next instanceof \DOMText) {
                # 3. Replace data with node, node's data's length, 0, and next's data.
                $previous->data .= $next->data;

                # 4. If next's parent is non-null, then remove next.
                // DEVIATION: There are no mutation events in this implementation, so there's no
                // reason to check for a parent here.
                $next->parentNode->removeChild($next);
            }
        }
    }

    protected function __get_spellcheck(): bool {
        # The spellcheck IDL attribute, on getting, must return true if the element's
        # spellcheck content attribute is in the true state, or if the element's
        # spellcheck content attribute is in the default state and the element's default
        # behavior is true-by-default, or if the element's spellcheck content attribute
        # is in the default state and the element's default behavior is
        # inherit-by-default and the element's parent element's spellcheck IDL attribute
        # would return true; otherwise, if none of those conditions applies, then the
        # attribute must instead return false.

        // This user agent will be false-by-default.
        return ($this->getAttribute('spellcheck') === 'true');
    }

    protected function __set_spellcheck(bool $value): void {
        # On setting, if the new value is true, then the element's spellcheck content
        # attribute must be set to the literal string "true", otherwise it must be set
        # to the literal string "false".
        $this->setAttribute('spellcheck', ($value) ? 'true' : 'false');
    }

    protected function __get_title(): string {
        # The accessKey IDL attribute must reflect the title content attribute.
        return $this->getAttribute('title') ?? '';
    }

    protected function __set_title(string $value): void {
        $this->setAttribute('title', $value);
    }

    protected function __get_translate(): bool {
        # The translate IDL attribute must, on getting, return true if the element's
        # translation mode is translate-enabled, and false otherwise.

        # Each element (even non-HTML elements) has a translation mode, which is in
        # either the translate-enabled state or the no-translate state. If an HTML
        # element's translate attribute is in the yes state, then the element's
        # translation mode is in the translate-enabled state; otherwise, if the
        # element's translate attribute is in the no state, then the element's
        # translation mode is in the no-translate state. Otherwise, either the element's
        # translate attribute is in the inherit state, or the element is not an HTML
        # element and thus does not have a translate attribute; in either case, the
        # element's translation mode is in the same state as its parent element's, if
        # any, or in the translate-enabled state, if the element is a document element.

        $value = strtolower($this->getAttribute('translate') ?? '');
        if ($value === 'yes') {
            return true;
        } elseif ($value === 'no') {
            return false;
        }

        $n = $this->_innerNode;
        $doc = $n->ownerDocument;
        while ($n = $n->parentNode) {
            // This looks weird but it's faster to check for the method here first because
            // getting a wrapper node causes a wrapper element to be created if it doesn't
            // already exist. Don't want to create unnecessary wrappers.
            if (method_exists($n, 'getAttribute') && $n->getAttribute('translate') === 'yes' && $doc->getWrapperNode($n) instanceof HTMLElement) {
                return true;
            }
        }

        return false;
    }

    protected function __set_translate(bool $value): void {
        # On setting, it must set the content attribute's value to "yes" if the new
        # value is true, and set the content attribute's value to "no" otherwise.
        $this->setAttribute('translate', ($value) ? 'yes' : 'no');
    }


    protected function autoCapitalizationHint(\DOMElement $element): string {
        # To compute the own autocapitalization hint of an element element, run the
        # following steps:
        # 1. If the autocapitalize content attribute is present on element, and its
        #    value is not the empty string, return the state of the attribute.
        $value = $element->getAttribute('autocapitalize');
        if ($value !== null && $value !== '') {
            return $value;
        }

        # 2. If element is an autocapitalize-inheriting element and has a non-null form
        #    owner, return the own autocapitalization hint of element's form owner.
        elseif (in_array($element->tagName, [ 'button', 'fieldset', 'input', 'output', 'select', 'textarea' ])) {
            # A form-associated element can have a relationship with a form element, which
            # is called the element's form owner. If a form-associated element is not
            # associated with a form element, its form owner is said to be null.

            # A form-associated element is, by default, associated with its nearest ancestor
            # form element (as described below), but, if it is listed, may have a form
            # attribute specified to override this.

            $n = $element;
            while ($n = $n->parentNode) {
                if ($n->tagName === 'form' && $n->ownerDocument->getWrapperNode($n) instanceof HTMLElement) {
                    return $this->autoCapitalizationHint($n);
                }
            }
        }

        ## 3. Return default.
        return 'default';
    }
}