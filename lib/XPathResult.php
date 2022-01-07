<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\Framework\MagicProperties,
    MensBeam\HTML\DOM\Inner\Reflection;


class XPathResult implements \ArrayAccess, \Countable, \Iterator {
    use MagicProperties;

    public const ANY_TYPE = 0;
    public const NUMBER_TYPE = 1;
    public const STRING_TYPE = 2;
    public const BOOLEAN_TYPE = 3;
    public const UNORDERED_NODE_ITERATOR_TYPE = 4;
    public const ORDERED_NODE_ITERATOR_TYPE = 5;
    public const UNORDERED_NODE_SNAPSHOT_TYPE = 6;
    public const ORDERED_NODE_SNAPSHOT_TYPE = 7;
    public const ANY_UNORDERED_NODE_TYPE = 8;
    public const FIRST_ORDERED_NODE_TYPE = 9;

    protected bool $_invalidIteratorState = false;
    protected int $position = 0;
    protected int $_resultType;
    protected \DOMNodeList|array $storage;

    protected function __get_booleanValue(): bool {
        if ($this->_resultType !== self::BOOLEAN_TYPE) {
            throw new XPathException(XPathException::TYPE_ERROR);
        }

        return $this->storage[0];
    }

    protected function __get_invalidIteratorState(): bool {
        return $this->_invalidIteratorState;
    }

    protected function __get_numberValue(): float {
        if ($this->_resultType !== self::NUMBER_TYPE) {
            throw new XPathException(XPathException::TYPE_ERROR);
        }

        return $this->storage[0];
    }

    protected function __get_resultType(): int {
        return $this->_resultType;
    }

    protected function __get_singleNodeValue(): Node {
        if (!in_array($this->_resultType, [ self::ANY_UNORDERED_NODE_TYPE, self::FIRST_ORDERED_NODE_TYPE ])) {
            throw new XPathException(XPathException::TYPE_ERROR);
        }

        $node = $this->storage[0];
        return $node->ownerDocument->getWrapperNode($node);
    }

    protected function __get_snapshotLength(): int {
        if (!in_array($this->_resultType, [ self::ORDERED_NODE_SNAPSHOT_TYPE, self::UNORDERED_NODE_SNAPSHOT_TYPE ])) {
            throw new XPathException(XPathException::TYPE_ERROR);
        }

        return $this->count();
    }

    protected function __get_stringValue(): string {
        if ($this->_resultType !== self::STRING_TYPE) {
            throw new XPathException(XPathException::TYPE_ERROR);
        }

        return $this->storage[0];
    }


    protected function __construct(int $type, \DOMNodeList|array $object) {
        $this->storage = $object;
        $this->_resultType = $type;
    }


    public function count(): int {
        $this->validateStorage();
        return (is_array($this->storage)) ? count($this->storage) : $this->storage->length;
    }

    public function current(): ?Node {
        $this->validateStorage();
        $node = $this->storage[$this->position];
        return $node->ownerDocument->getWrapperNode($node);
    }

    public function iterateNext(): ?Node {
        if (!in_array($this->_resultType, [ self::ORDERED_NODE_ITERATOR_TYPE, self::UNORDERED_NODE_ITERATOR_TYPE ])) {
            throw new XPathException(XPathException::TYPE_ERROR);
        }

        if ($this->position + 1 > $this->count()) {
            return null;
        }

        $node = $this->storage[$this->position++];
        return $node->ownerDocument->getWrapperNode($node);
    }

    public function key(): int {
        $this->validateStorage();
        return $this->position;
    }

    public function next(): void {
        $this->validateStorage();
        $this->position++;
    }

    public function rewind(): void {
        $this->validateStorage();
        $this->position = 0;
    }

    public function offsetExists($offset): bool {
        $this->validateStorage();
        return isset($this->storage[$offset]);
    }

    public function offsetGet($offset): ?Node {
        $this->validateStorage();

        $node = $this->storage[$this->position];
        return ($node !== null) ? $node->ownerDocument->getWrapperNode($node) : null;
    }

    public function offsetSet($offset, $value): void {
        $this->validateStorage();
    }

    public function offsetUnset($offset): void {
        $this->validateStorage();
    }

    public function snapshotItem(int $index): ?Node {
        if (!in_array($this->_resultType, [ self::ORDERED_NODE_SNAPSHOT_TYPE, self::UNORDERED_NODE_SNAPSHOT_TYPE ])) {
            throw new XPathException(XPathException::TYPE_ERROR);
        }

        if (!isset($this->storage[$index])) {
            return null;
        }

        $node = $this->storage[$index];
        return $node->ownerDocument->getWrapperNode($node);
    }

    public function valid(): bool {
        $this->validateStorage();
        return $this->offsetExists($this->position);
    }


    protected function validateStorage(): void {
        if (!in_array($this->_resultType, [ self::ORDERED_NODE_ITERATOR_TYPE, self::UNORDERED_NODE_ITERATOR_TYPE, self::ORDERED_NODE_SNAPSHOT_TYPE, self::UNORDERED_NODE_SNAPSHOT_TYPE ])) {
            throw new XPathException(XPathException::TYPE_ERROR);
        }
    }
}