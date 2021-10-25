<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\Parser;


class Element extends Node {
    use ChildNode, DocumentOrElement, ParentNode;

    protected function __get_namespaceURI(): string {
        // PHP's DOM uses null incorrectly for the HTML namespace, and if you attempt to
        // use the HTML namespace anyway it has additional bugs we don't have to work
        // around because of the wrapper classes; So, use the null namespace internally
        // but print out the HTML namespace instead.
        $namespace = $this->innerNode->namespaceURI;
        $doc = $this->ownerDocument;
        return (($doc instanceof Document && !$doc instanceof XMLDocument) && $namespace === null) ? Parser::HTML_NAMESPACE : $namespace;
    }


    protected function __construct(\DOMElement $element) {
        parent::__construct($element);
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

    public function setAttributeNode(Attr $attr): ?Attr {
        # The setAttributeNode(attr) and setAttributeNodeNS(attr) methods steps are to
        # return the result of setting an attribute given attr and this.
        $this->innerNode->setAttributeNode($this->getInnerNode($attr));
        return $attr;
    }

    public function setAttributeNodeNS(Attr $attr): ?Attr {
        return $this->setAttributeNode($attr);
    }
}