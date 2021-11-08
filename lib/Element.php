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
use MensBeam\HTML\Parser;


class Element extends Node {
    use ChildNode, DocumentOrElement, ParentNode;

    protected function __get_attributes(): NamedNodeMap {
        // NamedNodeMaps cannot be created from their constructors normally.
        return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\NamedNodeMap', $this, ($this instanceof Document) ? $this->innerNode : $this->innerNode->ownerDocument, $this->innerNode->attributes);
    }

    protected function __get_id(): string {
        # The id attribute must reflect the "id" content attribute.
        # Return the result of running get an attribute value given this and name.
        return $this->getAttribute('id') ?? '';
    }

    protected function __set_id(string $value): string {
        # The id attribute must reflect the "id" content attribute.
        # Set an attribute value for this using name and the given value.
        return $this->setAttribute('id', $value);
    }

    protected function __get_localName(): ?string {
        // PHP's DOM does this correctly already.
        return $this->innerNode->localName;
    }

    protected function __get_namespaceURI(): string {
        // PHP's DOM uses null incorrectly for the HTML namespace, and if you attempt to
        // use the HTML namespace anyway it has additional bugs we don't have to work
        // around because of the wrapper classes; So, use the null namespace internally
        // but print out the HTML namespace instead.
        $namespace = $this->innerNode->namespaceURI;
        $doc = $this->ownerDocument;
        return (!$doc instanceof XMLDocument && $namespace === null) ? Parser::HTML_NAMESPACE : $namespace;
    }

    protected function __get_prefix(): ?string {
        $prefix = $this->innerNode->prefix;
        return ($prefix !== '') ? $prefix : null;
    }

    protected function __get_tagName(): string {
        return ($this->prefix === null) ? $this->localName : "{$this->prefix}:{$this->localName}";
    }


    protected function __construct(\DOMElement $element) {
        parent::__construct($element);
    }


    public function getAttributeNames(): array {
        # The getAttributeNames() method steps are to return the qualified names of the
        # attributes in this’s attribute list, in order; otherwise a new list.
        $list = [];
        $attributes = $this->innerNode->attributes;
        if ($attributes !== null) {
            foreach ($attributes as $attr) {
                $list[] = $attr->nodeName;
            }
        }

        return $list;
    }

    public function getAttributeNS(string $namespace, string $localName): ?string {
        return $this->innerNode->getAttributeNS($namespace, $localName);
    }

    public function getAttributeNode(string $qualifiedName): ?Attr {
        # The getAttributeNode(qualifiedName) method steps are to return the result of
        # getting an attribute given qualifiedName and this.
        #
        # To get an attribute by name given a qualifiedName and element element, run
        # these steps:
        #
        # 1. If element is in the HTML namespace and its node document is an HTML document,
        #    then set qualifiedName to qualifiedName in ASCII lowercase.
        // Document will always be an HTML document
        if (!$this instanceof XMLDocument && $this->namespaceURI === Parser::HTML_NAMESPACE) {
            $qualifiedName = strtolower($qualifiedName);
        }

        # 2. Return the first attribute in element’s attribute list whose qualified name is
        #    qualifiedName; otherwise null.
        // Going to try to handle this by getting the PHP DOM to do the heavy lifting
        // when we can because it's faster.
        $attr = $this->innerNode->getAttributeNode($qualifiedName);
        if ($attr === false) {
            // Replace any offending characters with "UHHHHHH" where H are the uppercase
            // hexadecimal digits of the character's code point
            $qualifiedName = $this->coerceName($qualifiedName);

            $attributes = $this->innerNode->attributes;
            foreach ($attributes as $a) {
                if ($a->nodeName === $qualifiedName) {
                    return $this->innerNode->ownerDocument->getWrapperNode($a);
                }
            }
            return null;
        }

        return ($attr !== false) ? $this->innerNode->ownerDocument->getWrapperNode($attr) : null;
    }

    public function hasAttributes(): bool {
        # The hasAttributes() method steps are to return false if this’s attribute list
        # is empty; otherwise true.
        // PHP's DOM does this correctly already.
        return $this->innerNode->hasAttributes();
    }

    public function hasAttribute(string $qualifiedName): bool {
        # The hasAttribute(qualifiedName) method steps are:
        #
        # 1. If this is in the HTML namespace and its node document is an HTML document,
        #    then set qualifiedName to qualifiedName in ASCII lowercase.
        if (!$this->ownerDocument instanceof XMLDocument && $this->namespaceURI === Parser::HTML_NAMESPACE) {
            $qualifiedName = strtolower($qualifiedName);
        }

        # 2. Return true if this has an attribute whose qualified name is qualifiedName;
        #    otherwise false.
        # An element has an attribute A if its attribute list contains A.
        // Going to try to handle this by getting the PHP DOM to do the heavy lifting
        // when we can because it's faster.
        $value = $this->innerNode->hasAttribute($qualifiedName);
        if (!$value) {
            // PHP DOM does not acknowledge the presence of XMLNS-namespace attributes,
            // so try it again just in case; getAttributeNode will coerce names if
            // necessary, too.
            $value = ($this->getAttributeNode($qualifiedName) !== null);
        }

        return $value;
    }

    public function setAttribute(string $qualifiedName, string $value): void {
        # 1. If qualifiedName does not match the Name production in XML, then throw an
        #    "InvalidCharacterError" DOMException.
        if (preg_match(InnerDocument::NAME_PRODUCTION_REGEX, $qualifiedName) !== 1) {
            throw new DOMException(DOMException::INVALID_CHARACTER);
        }

        # 2. If this is in the HTML namespace and its node document is an HTML document,
        #    then set qualifiedName to qualifiedName in ASCII lowercase.
        if (!$this instanceof XMLDocument) {
            $qualifiedName = strtolower($qualifiedName);
        }

        # 3. Let attribute be the first attribute in this’s attribute list whose
        #    qualified name is qualifiedName, and null otherwise.
        # 4. If attribute is null, create an attribute whose local name is qualifiedName,
        #    value is value, and node document is this’s node document, then append this
        #    attribute to this, and then return.
        # 5. Change attribute to value.
        // Going to try to handle this by getting the PHP DOM to do the heavy lifting
        // when we can because it's faster.
        try {
            $this->innerNode->setAttributeNS(null, $qualifiedName, $value);
        } catch (\DOMException $e) {
            // The attribute name is invalid for XML
            // Replace any offending characters with "UHHHHHH" where H are the uppercase
            // hexadecimal digits of the character's code point
            $this->innerNode->setAttributeNS(null, $this->coerceName($qualifiedName), $value);
        }

        // If you create an id attribute it won't be used by PHP in getElementById, so
        // let's fix that.
        if ($qualifiedName === 'id') {
            $this->innerNode->setIdAttribute($qualifiedName, true);
        }
    }

    public function setAttributeNode(Attr $attr): ?Attr {
        $innerNode = $this->innerNode;
        $innerAttr = $this->getInnerNode($attr);

        # The setAttributeNode(attr) and setAttributeNodeNS(attr) methods steps are to
        # return the result of setting an attribute given attr and this.
        $this->innerNode->setAttributeNode($innerAttr);

        // If you create an id attribute it won't be used by PHP in getElementById, so
        // let's fix that.
        if ($innerAttr->namespaceURI === null && $innerAttr->localName === 'id') {
            $innerNode->setIdAttributeNode($attr, true);
        }

        return $attr;
    }

    public function setAttributeNodeNS(Attr $attr): ?Attr {
        return $this->setAttributeNode($attr);
    }

    public function setAttributeNS(?string $namespace, string $qualifiedName, string $value): void {
        # 1. Let namespace, prefix, and localName be the result of passing namespace and
        #    qualifiedName to validate and extract.
        [ 'namespace' => $namespace, 'prefix' => $prefix, 'localName' => $localName ] = $this->validateAndExtract($qualifiedName, $namespace);
        $qualifiedName = ($prefix === null || $prefix === '') ? $localName : "{$prefix}:{$localName}";

        # 2. Set an attribute value for this using localName, value, and also prefix and
        #    namespace.
        // Going to try to handle this by getting the PHP DOM to do the heavy lifting
        // when we can because it's faster.
        // NOTE: We create attribute nodes so that xmlns attributes don't get lost;
        // otherwise they cannot be serialized
        if ($namespace === Parser::XMLNS_NAMESPACE) {
            // Xmlns attributes have special bugs just for them. How lucky! Xmlns attribute
            // nodes won't stick and can actually cause segmentation faults if created on a
            // no longer existing document element, appended to another element, and then
            // retrieved. So, use the methods used in Document::createAttributeNS to get an
            // attribute node.
            $a = $this->ownerDocument->createAttributeNS($namespace, $qualifiedName);

            $a->value = $this->escapeString($value, true);
            $this->setAttributeNodeNS($a);
        } else {
            try {
                $this->innerNode->setAttributeNS($namespace, $qualifiedName, $value);
            } catch (\DOMException $e) {
                // The attribute name is invalid for XML
                // Replace any offending characters with "UHHHHHH" where H are the
                // uppercase hexadecimal digits of the character's code point
                if ($prefix !== null) {
                    $qualifiedName = $this->coerceName($prefix) . ':' . $this->coerceName($localName);
                } else {
                    $qualifiedName = $this->coerceName($qualifiedName);
                }

                $this->innerNode->setAttributeNS($namespace, $qualifiedName, $value);
            }
        }

        if ($namespace === null) {
            // If you create an id attribute this way it won't be used by PHP in
            // getElementById, so let's fix that.
            if ($qualifiedName === 'id') {
                $this->innerNode->setIdAttribute($qualifiedName, true);
            }
        }
    }
}