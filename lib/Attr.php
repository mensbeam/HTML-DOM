<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


class Attr extends Node {
    protected function __get_localName(): string {
        // PHP's DOM does this correctly already.
        // Need to uncoerce string if necessary.
        $localName = $this->innerNode->localName;
        return (!str_contains(needle: 'U', haystack: $localName)) ? $localName : $this->uncoerceName($localName);
    }

    protected function __get_name(): string {
        // PHP's DOM does this correctly already.
        // Need to uncoerce string if necessary.
        $name = $this->innerNode->name;
        return (!str_contains(needle: 'U', haystack: $name)) ? $name : $this->uncoerceName($name);
    }

    protected function __get_namespaceURI(): ?string {
        // PHP's DOM does this correctly already.
        return $this->innerNode->namespaceURI;
    }

    protected function __get_ownerElement(): Element {
        // PHP's DOM does this correctly already.
        $wrapperNode = $this->innerNode->ownerDocument->getWrapperNode($this->innerNode->ownerElement);
        return $wrapperNode;
    }

    protected function __get_prefix(): string {
        // PHP's DOM does this correctly already.
        // Need to uncoerce string if necessary.
        $prefix = $this->innerNode->prefix;
        return (!str_contains(needle: 'U', haystack: $prefix)) ? $prefix : $this->uncoerceName($prefix);
    }

    protected function __get_specified(): bool {
        # Useless, always returns true
        return true;
    }

    protected function __get_value(): string {
        // PHP's DOM does this correctly already.
        return $this->innerNode->value;
    }

    protected function __set_value(string $value) {
        // PHP's DOM does this correctly already.
        $this->innerNode->value = $value;
    }
}