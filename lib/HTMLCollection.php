<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\Framework\MagicProperties,
    MensBeam\HTML\Parser;


# An HTMLCollection object is a collection of elements.
#
# NOTE: HTMLCollection is a historical artifact we cannot rid the web of. While
# developers are of course welcome to keep using it, new API standard designers
# ought not to use it (use sequence<T> in IDL instead).
#
# A collection is an object that represents a list of nodes. A collection can be
# either live or static. Unless otherwise stated, a collection must be live.
#
# If a collection is live, then the attributes and methods on that object must
# operate on the actual underlying data, not a snapshot of the data.
#
# When a collection is created, a filter and a root are associated with it.
#
# The collection then represents a view of the subtree rooted at the
# collection’s root, containing only nodes that match the given filter. The view
# is linear. In the absence of specific requirements to the contrary, the nodes
# within the collection must be sorted in tree order.
class HTMLCollection implements \ArrayAccess, \Countable, \Iterator {
    use MagicProperties;

    protected ?\Closure $filter = null;
    protected int $_length = 0;
    protected ?array $nodeArray = null;
    protected int $position = 0;


    protected function __get_length(): int {
        # The length attribute must return the number of nodes represented by the
        # collection.
        return $this->count();
    }


    protected function __construct(array|\Closure $arrayOrClosure = []) {
        // In this implementation the root part of the creation is handled either before
        // the NodeList is created (array) or within the filter (\Closure).
        if ($arrayOrClosure === null) {
            $arrayOrClosure = [];
        }

        if (is_callable($arrayOrClosure)) {
            $this->filter = $arrayOrClosure;
        } else {
            // Check types while also unpacking the iterable.
            $array = [];
            foreach ($arrayOrClosure as $i) {
                if (!$i instanceof Element) {
                    $type = gettype($i);
                    if ($type === 'object') {
                        $type = get_class($i);
                    }
                    throw new Exception(Exception::ARGUMENT_TYPE_ERROR, 1, 'arrayOrClosure', 'array<Element>|\\Closure<array<Element>>', $type);
                }

                $array[] = $i;
            }

            $this->nodeArray = $array;
            $this->_length = count($array);
        }
    }

    public function count(): int {
        if ($this->nodeArray !== null) {
            return $this->_length;
        }

        $nodeArray = ($this->filter)();
        return count($nodeArray);
    }

    public function current(): ?Element {
        return $this->item($this->position);
    }

    public function item(int $index): ?Element {
        # The item(index) method must return the indexth node in the collection. If
        # there is no indexth node in the collection, then the method must return null.
        if ($index >= $this->count()) {
            return null;
        }

        $nodeArray = ($this->nodeArray !== null) ? $this->nodeArray : ($this->filter)();
        if (array_key_exists($index, $nodeArray)) {
            return $nodeArray[$index];
        }

        return null;
    }

    public function key(): int {
        return $this->position;
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
        $nodeArray = ($this->nodeArray !== null) ? $this->nodeArray : ($this->filter)();
        foreach ($nodeArray as $element) {
            if ($element->getAttribute('id') === $name || $element->namespaceURI === Parser::HTML_NAMESPACE && $element->getAttribute('name') === $name) {
                return $element;
            }
        }

        return null;
    }

    public function next(): void {
        $this->position++;
    }

    public function rewind(): void {
        $this->position = 0;
    }

    public function offsetExists($offset): bool {
        if (is_int($offset)) {
            $nodeArray = ($this->nodeArray !== null) ? $this->nodeArray : ($this->filter)();
            return array_key_exists($offset, $nodeArray);
        }

        return ($this->namedItem($offset) !== null);
    }

    public function offsetGet($offset): ?Element {
        return (is_int($offset)) ? $this->item($offset) : $this->namedItem($offset);
    }

    public function offsetSet($offset, $value): void {
        // NodeLists are immutable; the spec is ambiguous as to what to do here.
        // Browsers silently fail here, so that's what we're going to do.
    }

    public function offsetUnset($offset): void {
        // NodeLists are immutable; the spec is ambiguous as to what to do here.
        // Browsers silently fail here, so that's what we're going to do.
    }

    public function valid() {
        $this->offsetExists($this->position);
    }
}