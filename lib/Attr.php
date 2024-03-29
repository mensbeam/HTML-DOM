<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


/** @property \DOMAttr $_innerNode */
class Attr extends Node {
    protected \DOMAttr $_innerNode;

    protected function __get_localName(): string {
        // PHP's DOM does this correctly already.
        // Need to uncoerce string if necessary.
        $localName = $this->_innerNode->localName;
        return (!str_contains(needle: 'U', haystack: $localName)) ? $localName : $this->uncoerceName($localName);
    }

    protected function __get_name(): string {
        // PHP's DOM incorrectly returns the local name instead of the qualified name
        // per the specification.
        // Need to uncoerce string if necessary.
        $name = $this->_innerNode->nodeName;
        return (!str_contains(needle: 'U', haystack: $name)) ? $name : $this->uncoerceName($name);
    }

    protected function __get_namespaceURI(): ?string {
        return $this->_innerNode->namespaceURI;
    }

    protected function __get_ownerElement(): ?Element {
        // PHP's DOM does this correctly already.
        $innerOwnerElement = $this->_innerNode->ownerElement;

        if ($innerOwnerElement === null) {
            return null;
        }

        /** @var MensBeam\HTML\DOM\Inner\Document $innerOwnerDocument */
        $innerOwnerDocument = $this->_innerNode->ownerDocument;
        return $innerOwnerDocument->getWrapperNode($this->_innerNode->ownerElement);
    }

    protected function __get_prefix(): string {
        // PHP's DOM does this correctly already.
        // Need to uncoerce string if necessary.
        $prefix = $this->_innerNode->prefix;
        return (!str_contains(needle: 'U', haystack: $prefix)) ? $prefix : $this->uncoerceName($prefix);
    }

    protected function __get_specified(): bool {
        # Useless, always returns true
        return true;
    }

    protected function __get_value(): string {
        // PHP's DOM does this correctly already.
        return $this->_innerNode->value;
    }

    protected function __set_value(string $value) {
        // PHP's DOM does this correctly already.
        $this->_innerNode->value = $value;
    }
}