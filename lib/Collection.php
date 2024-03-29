<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\GettersAndSetters,
    MensBeam\HTML\DOM\Inner\Document as InnerDocument;



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
/**
 * Not in standard except as an abstract description of HTMLCollection and
 * NodeList. Exists to eliminate code duplication between HTMLCollection and
 * NodeList.
 */
abstract class Collection implements \ArrayAccess, \Countable, \Iterator {
    use GettersAndSetters;

    protected InnerDocument $innerDocument;
    protected \DOMNodeList|\DOMNamedNodeMap $innerCollection;
    protected ?\Closure $filter = null;
    protected int $_length = 0;
    protected ?array $nodeArray = null;
    protected int $position = 0;


    protected function __get_length(): int {
        # The length attribute must return the number of nodes represented by the
        # collection.
        return $this->count();
    }


    protected function __construct(InnerDocument $innerDocument, \DOMNodeList $nodeList) {
        $this->innerDocument = $innerDocument;
        $this->innerCollection = $nodeList;
    }


    public function count(): int {
        return $this->innerCollection->length;
    }

    public function current(): ?Node {
        return $this->item($this->position);
    }

    public function item(int $index): ?Node {
        # The item(index) method must return the indexth node in the collection. If
        # there is no indexth node in the collection, then the method must return null.
        // PHP's DOM does this okay already
        $node = $this->innerCollection->item($index);
        if ($node === null) {
            return null;
        }

        return $this->innerDocument->getWrapperNode($node);
    }

    public function key(): int {
        return $this->position;
    }

    public function next(): void {
        $this->position++;
    }

    public function rewind(): void {
        $this->position = 0;
    }

    public function offsetExists($offset): bool {
        return ($this->innerCollection->item($offset) !== null);
    }

    public function offsetGet($offset): ?Node {
        return $this->item($offset);
    }

    public function offsetSet($offset, $value): void {
        // Collections are immutable; the spec is ambiguous as to what to do here.
        // Browsers silently fail here, so that's what we're going to do.
    }

    public function offsetUnset($offset): void {
        // Collections are immutable; the spec is ambiguous as to what to do here.
        // Browsers silently fail here, so that's what we're going to do.
    }

    public function valid(): bool {
        return $this->offsetExists($this->position);
    }
}