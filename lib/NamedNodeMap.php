<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\Inner\{
    Document as InnerDocument,
    Reflection
};
use MensBeam\HTML\Parser\NameCoercion;


class NamedNodeMap extends Collection {
    use NameCoercion;

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

    public function offsetGet($offset): ?Attr {
        if (is_int($offset)) {
            return $this->item($offset);
        }

        # A NamedNodeMap object’s supported property names are the return value of running
        # these steps:
        # 1. Let names be the qualified names of the attributes in this NamedNodeMap object’s
        #    attribute list, with duplicates omitted, in order.
        // The spec is extremely vague as to what to do here, but it seems to expect
        // this to be some sort of live private property that the class will poll to
        // check for valid property names when trying to access them. This is
        // inefficient. Going to do basically the same thing but not return a list of
        // every one. It will just search the element's attribute list instead using the
        // same process.

        # 2. If this NamedNodeMap object’s element is in the HTML namespace and its node
        #    document is an HTML document, then for each name in names:
        #    1. Let lowercaseName be name, in ASCII lowercase.
        #    2. If lowercaseName is not equal to name, remove name from names.
        # 3. Return names.

        $innerElement = Reflection::getProtectedProperty($this->element, 'innerNode');
        $innerDocument = $innerElement->ownerDocument;
        $attributes = $innerElement->attributes;
        if ($attributes->length > 0) {
            $coercedOffset = $this->coerceName($offset);

            foreach ($attributes as $attr) {
                $name = $attr->nodeName;
                if ($this->element->namespaceURI === Node::HTML_NAMESPACE && $name !== strtolower($name)) {
                    continue;
                }

                if ($name === $offset || $name === $coercedOffset) {
                    return $innerDocument->getWrapperNode($attr);
                }
            }
        }

        return null;
    }

    public function offsetExists($offset): bool {
        return (((is_int($offset)) ? $this->item($offset) : $this->getNamedItem($offset)) !== null);
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