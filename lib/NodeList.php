<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\Framework\MagicProperties;


/** Exists because PHP DOM's DOMNodeList is always live. */
class NodeList implements \ArrayAccess, \Countable, \Iterator {
    use MagicProperties;

    protected int $_length = 0;
    protected int $position = 0;
    protected array $storage = [];


    protected function __get_length(): int {
        # The length attribute must return the number of nodes represented by the
        # collection.
        return $this->_length;
    }


    public function __construct(iterable $iterable) {
        // Per the specification one cannot create a NodeList via its constructor, but
        // this implementation is not going to build up the framework for that.

        // Check types while also unpacking the traversable.
        $array = [];
        foreach ($iterable as $i) {
            if (!$i instanceof Node && !$i instanceof \DOMDocumentType) {
                $type = gettype($i);
                if ($type === 'object') {
                    $type = get_class($i);
                }
                throw new Exception(Exception::ARGUMENT_TYPE_ERROR, 1, 'traversable', 'Node|\\DOMDocumentType', $type);
            }

            $array[] = $i;
        }

        $this->storage = $array;
        $this->_length = count($array);
    }

    public function count(): int {
        return $this->_length;
    }

    public function current(): Node|\DOMDocumentType|null {
        return $this->item($this->position);
    }

    public function item(int $index): Node|\DOMDocumentType|null {
        # The item(index) method must return the indexth node in the collection. If
        # there is no indexth node in the collection, then the method must return null.
        if ($index >= $this->_length) {
            return null;
        }

        return $this->storage[$index];
    }

    public function key(): int {
        return $this->position;
    }

    public function next(): void {
        ++$this->position;
    }

    public function rewind(): void {
        $this->position = 0;
    }

    public function offsetExists($offset): bool {
        return isset($this->storage[$offset]);
    }

    public function offsetGet($offset): Node|\DOMDocumentType|null {
        return $this->item($offset);
    }

    public function offsetSet($offset, $value): void {
        // NodeLists are immutable
    }

    public function offsetUnset($offset): void {
        // Nodelists are immutable
    }

    public function valid() {
        return array_key_exists($this->position, $this->storage);
    }
}
