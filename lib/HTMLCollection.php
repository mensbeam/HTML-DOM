<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\Parser;


# An HTMLCollection object is a collection of elements.
class HTMLCollection extends Collection {
    public function current(): ?Element {
        return parent::current();
    }

    public function item(int $index): ?Element {
        return parent::item($index);
    }

    public function namedItem(string $name): ?Element {
        # The namedItem(key) method steps are:
        // The interface says to use "name" as the argument while the steps below show
        // to use "key" *shrug*.
        # 1. If key is the empty string, return null.
        if ($name === '') {
            return null;
        }

        # 2. Return the first element in the collection for which at least one of the following is true:
        #       • it has an ID which is key;
        #       • it is in the HTML namespace and has a name attribute whose value is key;
        #    or null if there is no such element.
        foreach ($this->innerCollection as $element) {
            if ($element->getAttribute('id') === $name || $element->namespaceURI === null && $element->getAttribute('name') === $name) {
                return $this->innerDocument->getWrapperNode($element);
            }
        }

        return null;
    }

    public function offsetGet($offset): ?Element {
        return (is_int($offset)) ? $this->item($offset) : $this->namedItem($offset);
    }
}