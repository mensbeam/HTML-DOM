<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


class DocumentFragment extends \DOMDocumentFragment {
    use MagicProperties, ParentNode, Walk;

    protected $_host = null;

    protected function __get_host(): ?\DOMNode {
        if ($this->_host === null) {
            return $this->_host;
        }

        return $this->_host->get();
    }

    protected function __set_host(\DOMNode $value) {
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


    public function __toString() {
        return $this->ownerDocument->saveHTML($this);
    }
}
