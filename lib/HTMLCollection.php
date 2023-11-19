<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


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
        if (is_int($offset)) {
            return $this->item($offset);
        }

        # The supported property names are the values from the list returned by these
        # steps:
        # 1. Let result be an empty list.
        # 2. For each element represented by the collection, in tree order:
        #    1. If element has an ID which is not in result, append element’s ID to
        #       result.
        #    2. If element is in the HTML namespace and has a name attribute whose value is
        #       neither the empty string nor is in result, append element’s name attribute value
        #       to result.
        # 3. Return result.
        // The spec is extremely vague as to what to do here, but it seems to expect
        // this to be some sort of live private property that the class will poll to
        // check for valid property names when trying to access them. This is
        // inefficient. Going to do basically the same thing but not return a list of
        // every one. It will just search the list instead using the same process.

        $document = $this->innerDocument->wrapperNode;
        foreach ($this->innerCollection as $node) {
            if ($node->getAttribute('id') === $offset) {
                return $this->innerDocument->getWrapperNode($node);
            }
        }

        foreach ($this->innerCollection as $node) {
            if (!$document instanceof XMLDocument && $node->namespaceURI === null && $node->getAttribute('name') === $offset) {
                return $this->innerDocument->getWrapperNode($node);
            }
        }

        return null;
    }

    public function offsetExists($offset): bool {
        return ($this->offsetGet($offset) !== null);
    }
}