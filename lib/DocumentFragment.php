<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\Framework\MagicProperties;


class DocumentFragment extends \DOMDocumentFragment implements Node {
    use MagicProperties, ParentNode;

    protected ?\WeakReference $_host = null;

    protected function __get_host(): ?HTMLTemplateElement {
        if ($this->_host === null) {
            return null;
        }

        return $this->_host->get();
    }

    protected function __set_host(HTMLTemplateElement $value) {
        if ($this->_host !== null) {
            throw new Exception(Exception::READONLY_PROPERTY, 'host');
        }

        // Check to see if this is being set within the HTMLTemplateElement constructor
        // and throw a read only exception otherwise. This will ensure the host remains
        // readonly. YES. THIS IS DIRTY. We shouldn't do this, but there is no other
        // option. While DocumentFragment could be created via a constructor it cannot
        // be associated with a document unless created by
        // Document::createDocumentFragment.
        $backtrace = debug_backtrace();
        $okay = false;
        for ($len = count($backtrace), $i = $len - 1; $i >= 0; $i--) {
            $cur = $backtrace[$i];
            if ($cur['function'] === '__construct' && $cur['class'] === __NAMESPACE__ . '\\HTMLTemplateElement') {
                $okay = true;
                break;
            }
        }

        if (!$okay) {
            throw new Exception(Exception::READONLY_PROPERTY, 'host');
        }

        $this->_host = \WeakReference::create($value);
    }


    public function getElementById(string $elementId): ?Element {
        # The getElementById(elementId) method steps are to return the first element, in
        # tree order, within this’s descendants, whose ID is elementId; otherwise, if
        # there is no such element, null.
        // This method is supposed to be within a NonElementParentNode trait, but
        // Document has an adequate implementation already from PHP DOM. It doesn't,
        // however, implement one for \DOMDocumentFragment, so here goes.
        return $this->walk(function($n) use($elementId) {
            return ($n instanceof Element && $n->getAttribute('id') === $elementId);
        })->current();
    }


    public function __toString() {
        return $this->ownerDocument->saveHTML($this);
    }
}
