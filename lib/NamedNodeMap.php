<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\InnerNode\Document as InnerDocument;


class NamedNodeMap extends Collection {
    # A NamedNodeMap has an associated element (an element).
    protected Element $element;


    protected function __construct(Element $element, InnerDocument $innerDocument, ?\DOMNamedNodeMap $namedNodeMap) {
        $this->element = $element;
        $this->innerDocument = $innerDocument;
        // Have to check for null because PHP DOM violates the spec and returns null when empty
        $this->innerCollection = $namedNodeMap ?? new \DOMNamedNodeMap();
    }


    public function current(): ?Attr {
        return parent::current();
    }

    public function getNamedItem(string $qualifiedName): ?Attr {
        # The getNamedItem(qualifiedName) method steps are to return the result of
        # getting an attribute given qualifiedName and element.
        return $this->element->getAttributeNode($qualifiedName);
    }

    public function getNamedItemNS(?string $namespace, string $localName): ?Attr {
        # The getNamedItemNS(namespace, localName) method steps are to return the result
        # of getting an attribute given namespace, localName, and element.
        return $this->element->getAttributeNodeNS($namespace, $localName);
    }

    public function item(int $index): ?Attr {
        return parent::item($index);
    }

    public function removeNamedItem(string $qualifiedName): ?Attr {
        return $this->removeNamedItemNS(null, $qualifiedName);
    }

    public function removeNamedItemNS(?string $namespace, string $localName): ?Attr {
        # The removeNamedItem(qualifiedName) method steps are:
        #
        # 1. Let attr be the result of removing an attribute given namespace, localName,
        # and element.
        $attr = $this->element->removeAttributeNode($namespace, $localName);

        # 2. If attr is null, then throw a "NotFoundError" DOMException.
        if ($attr === null) {
            throw new DOMException(DOMException::NOT_FOUND);
        }

        # 3. Return attr.
        return $attr;
    }

    public function setNamedItem(string $attr): ?Attr {
        # The setNamedItem(attr) and setNamedItemNS(attr) method steps are to return the
        # result of setting an attribute given attr and element.
        return $this->element->setAttributeNode($attr);
    }

    public function setNamedItemNS(string $attr): ?Attr {
        # The setNamedItem(attr) and setNamedItemNS(attr) method steps are to return the
        # result of setting an attribute given attr and element.
        return $this->element->setAttributeNode($attr);
    }
}