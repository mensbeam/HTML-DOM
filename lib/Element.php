<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\Framework\MagicProperties;
use MensBeam\HTML\Parser;


class Element extends \DOMElement {
    use ChildNode, DocumentOrElement, MagicProperties, Moonwalk, ParentNode, ToString, Walk;


    protected function __get_classList(): TokenList {
        return new TokenList($this, 'class');
    }

    protected function __get_innerHTML(): string {
        ### DOM Parsing Specification ###
        # 2.3 The InnerHTML mixin
        #
        # On getting, return the result of invoking the fragment serializing algorithm
        # on the context object providing true for the require well-formed flag (this
        # might throw an exception instead of returning a string).
        // DEVIATION: Parsing of XML documents will not be handled by this
        // implementation, so there's no need for the well-formed flag.
        return $this->ownerDocument->saveHTML($this);
    }

    protected function __set_innerHTML(string $value) {
        ### DOM Parsing Specification ###
        # 2.3 The InnerHTML mixin
        #
        # On setting, these steps must be run:
        # 1. Let context element be the context object's host if the context object is a
        # ShadowRoot object, or the context object otherwise.
        // DEVIATION: There is no scripting in this implementation.

        # 2. Let fragment be the result of invoking the fragment parsing algorithm with
        # the new value as markup, and with context element.
        $fragment = Parser::parseFragment($this, $this->ownerDocument->quirksMode, $value, 'UTF-8');
        $fragment = $this->ownerDocument->importNode($fragment);

        # 3. If the context object is a template element, then let context object be the
        # template's template contents (a DocumentFragment).
        if ($this instanceof HTMLTemplateElement) {
            $this->content = $fragment;
        }
        # 4. Replace all with fragment within the context object.
        else {
            # To replace all with a node within a parent, run these steps:
            #
            # 1. Let removedNodes be parent’s children.
            // DEVIATION: removedNodes is used below for scripting. There is no scripting in
            // this implementation.

            # 2. Let addedNodes be parent’s children.
            // DEVIATION: addedNodes is used below for scripting. There is no scripting in
            // this implementation.

            # 3. If node is a DocumentFragment node, then set addedNodes to node’s
            # children.

            // DEVIATION: Again, there is no scripting in this implementation.
            # 4. Otherwise, if node is non-null, set addedNodes to « node ».
            // DEVIATION: Yet again, there is no scripting in this implementation.

            # 5. Remove all parent’s children, in tree order, with the suppress observers
            # flag set.
            // DEVIATION: There are no observers to suppress as there is no scripting in
            // this implementation.
            while ($this->hasChildNodes()) {
                $this->removeChild($this->firstChild);
            }

            # 6. Otherwise, if node is non-null, set addedNodes to « node ».
            # If node is non-null, then insert node into parent before null with the
            # suppress observers flag set.
            // DEVIATION: Yet again, there is no scripting in this implementation.

            # 7. If either addedNodes or removedNodes is not empty, then queue a tree
            # mutation record for parent with addedNodes, removedNodes, null, and null.
            // DEVIATION: Normally the tree mutation record would do the actual replacement,
            // but there is no scripting in this implementation. Going to simply append the
            // fragment instead.
            $this->appendChild($fragment);
        }
    }

    protected function __get_outerHTML(): string {
        ### DOM Parsing Specification ###
        # 2.4 Extensions to the Element interface
        # outerHTML
        #
        # On getting, return the result of invoking the fragment serializing algorithm
        # on a fictional node whose only child is the context object providing true for
        # the require well-formed flag (this might throw an exception instead of
        # returning a string).
        // DEVIATION: Parsing of XML documents will not be handled by this
        // implementation, so there's no need for the well-formed flag.
        return (string)$this;
    }

    protected function __set_outerHTML(string $value) {
        ### DOM Parsing Specification ###
        # 2.4 Extensions to the Element interface
        # outerHTML
        #
        # On setting, the following steps must be run:
        # 1. Let parent be the context object's parent.
        $parent = $this->parentNode;

        # 2. If parent is null, terminate these steps. There would be no way to obtain a
        # reference to the nodes created even if the remaining steps were run.
        if ($parent === null) {
            return;
        }
        # 3. If parent is a Document, throw a "NoModificationAllowedError" DOMException.
        elseif ($parent instanceof Document) {
            throw new DOMException(DOMException::NO_MODIFICATION_ALLOWED);
        }
        # 4. parent is a DocumentFragment, let parent be a new Element with:
        # • body as its local name,
        # • The HTML namespace as its namespace, and
        # • The context object's node document as its node document.
        elseif ($parent instanceof DocumentFragment) {
            $parent = $this->ownerDocument->createElement('body');
        }

        # 5. Let fragment be the result of invoking the fragment parsing algorithm with
        # the new value as markup, and parent as the context element.
        $fragment = Parser::parseFragment($parent, $this->ownerDocument->quirksMode, $value, 'UTF-8');
        $fragment = $this->ownerDocument->importNode($fragment);

        # 6. Replace the context object with fragment within the context object's
        # parent.
        $this->parentNode->replaceChild($fragment, $this);
    }


    public function getAttribute(string $qualifiedName): ?string {
        # The getAttribute(qualifiedName) method steps are:
        #
        # 1. Let attr be the result of getting an attribute given qualifiedName and this.
        $attr = $this->getAttributeNode($qualifiedName);
        # 2. If attr is null, return null.
        if ($attr === null) {
            return null;
        }
        # 3. Return attr’s value.
        // Uncoerce the value if necessary
        return (!strpos($attr->value, 'U')) ? $attr->value : $this->uncoerceName($attr->value);
    }

    public function getAttributeNames(): array {
        $result = [];
        foreach ($this->attributes as $a) {
            // Uncoerce names if necessary
            $result[] = (!strpos($a->nodeName, 'U')) ? $a->nodeName : $this->uncoerceName($a->nodeName);
        }

        return $result;
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
        if ($this->isHTMLNamespace()) {
            $qualifiedName = strtolower($qualifiedName);
        }

        # 2. Return the first attribute in element’s attribute list whose qualified name is
        #    qualifiedName; otherwise null.
        // Going to try to handle this by getting the PHP DOM to do the heavy lifting
        // when we can because it's faster.
        $attr = parent::getAttributeNode($qualifiedName);
        if ($attr === false) {
            // Replace any offending characters with "UHHHHHH" where H are the uppercase
            // hexadecimal digits of the character's code point
            $qualifiedName = $this->coerceName($qualifiedName);

            foreach ($this->attributes as $a) {
                if ($a->nodeName === $qualifiedName) {
                    return $a;
                }
            }
            return null;
        }

        return ($attr !== false) ? $attr : null;
    }

    public function getAttributeNodeNS(?string $namespace = null, string $localName): ?Attr {
        # The getAttributeNodeNS(namespace, localName) method steps are to return the
        # result of getting an attribute given namespace, localName, and this.
        #
        # To get an attribute by namespace and local name given a namespace, localName,
        # and element element, run these steps:
        #
        # 1. If namespace is the empty string, then set it to null.
        if ($namespace === '') {
            $namespace = null;
        }

        # 2. Return the attribute in element’s attribute list whose namespace is namespace
        #    and local name is localName, if any; otherwise null.
        // Going to try to handle this by getting the PHP DOM to do the heavy lifting
        // when we can because it's faster.
        $value = parent::getAttributeNodeNS($namespace, $localName);
        if (!$value) {
            // Replace any offending characters with "UHHHHHH" where H are the uppercase
            // hexadecimal digits of the character's code point
            $namespace = $this->coerceName($namespace ?? '');
            $localName = $this->coerceName($localName);

            // The PHP DOM does not acknowledge the presence of XMLNS-namespace attributes
            // sometimes, too... so this will get those as well in those circumstances.
            foreach ($this->attributes as $a) {
                if ($a->namespaceURI === $namespace && $a->localName === $localName) {
                    return $a;
                }
            }
            return null;
        }

        return ($value !== false) ? $value : null;
    }


    public function getAttributeNS(?string $namespace = null, string $localName): ?string {
        # The getAttributeNS(namespace, localName) method steps are:
        #
        # 1. Let attr be the result of getting an attribute given namespace, localName,
        #    and this.
        $attr = $this->getAttributeNodeNS($namespace, $localName);

        # 2. If attr is null, return null.
        if ($attr === null) {
            return null;
        }

        # 3. Return attr’s value.
        // Uncoerce the value if necessary
        return (!strpos($attr->value, 'U')) ? $attr->value : $this->uncoerceName($attr->value);
    }

    public function hasAttribute(string $qualifiedName): bool {
        # The hasAttribute(qualifiedName) method steps are:
        #
        # 1. If this is in the HTML namespace and its node document is an HTML document,
        #    then set qualifiedName to qualifiedName in ASCII lowercase.
        // Document will always be an HTML document
        if ($this->isHTMLNamespace()) {
            $qualifiedName = strtolower($qualifiedName);
        }

        # 2. Return true if this has an attribute whose qualified name is qualifiedName;
        #    otherwise false.
        # An element has an attribute A if its attribute list contains A.
        // Going to try to handle this by getting the PHP DOM to do the heavy lifting
        // when we can because it's faster.
        $value = parent::hasAttribute($qualifiedName);
        if (!$value) {
            // The PHP DOM does not acknowledge the presence of XMLNS-namespace attributes,
            // so try it again just in case; getAttributeNode will coerce names if
            // necessary, too.
            $value = ($this->getAttributeNode($qualifiedName) !== null);
        }

        return $value;
    }

    public function hasAttributeNS(?string $namespace = null, string $localName): bool {
        # The hasAttributeNS(namespace, localName) method steps are:
        #
        # 1. If namespace is the empty string, then set it to null.
        if ($namespace === '') {
            $namespace = null;
        }

        # 2. Return true if this has an attribute whose namespace is namespace and local name
        #    is localName; otherwise false.
        # An element has an attribute A if its attribute list contains A.
        // Going to try to handle this by getting the PHP DOM to do the heavy lifting
        // when we can because it's faster.
        $value = parent::hasAttributeNS($namespace, $localName);
        if (!$value) {
            // The PHP DOM does not acknowledge the presence of XMLNS-namespace attributes,
            // so try it again just in case; getAttributeNode will coerce names if
            // necessary, too.
            $value = ($this->getAttributeNodeNS($namespace, $localName) !== null);
        }

        return $value;
    }

    public function removeAttribute(string $qualifiedName): void {
        # The removeAttribute(qualifiedName) method steps are to remove an attribute
        # given qualifiedName and this, and then return undefined.
        #
        ## To remove an attribute by name given a qualifiedName and element element, run
        ## these steps:
        ##
        ## 1. Let attr be the result of getting an attribute given qualifiedName and element.
        $attr = $this->getAttributeNode($qualifiedName);
        ## 2. If attr is non-null, then remove attr.
        if ($attr !== null) {
            // Going to try to handle this by getting the PHP DOM to do the heavy lifting
            // when we can because it's faster.
            parent::removeAttributeNode($attr);

            // ClassList stuff because php garbage collection is... garbage.
            if ($qualifiedName === 'class') {
                ElementMap::delete($this);
            }
        }
        ## 3. Return attr.
        // Supposed to return undefined in the end, so let's skip this.
    }

    public function removeAttributeNS(?string $namespace, string $localName): bool {
        # The removeAttributeNS(namespace, localName) method steps are to remove an
        # attribute given namespace, localName, and this, and then return undefined.
        #
        ## To remove an attribute by namespace and local name given a namespace, localName, and element element, run these steps:
        ##
        ## 1. Let attr be the result of getting an attribute given namespace, localName, and element.
        $attr = $this->getAttributeNodeNS($namespace, $localName);
        ## 2. If attr is non-null, then remove attr.
        if ($attr !== null) {
            // Going to try to handle this by getting the PHP DOM to do the heavy lifting
            // when we can because it's faster.
            parent::removeAttributeNode($attr);

            // ClassList stuff because php garbage collection is... garbage.
            if ($qualifiedName === 'class') {
                ElementMap::delete($this);
            }
        }
        ## 3. Return attr.
        // Supposed to return undefined in the end, so let's skip this.
    }

    public function setAttribute(string $qualifiedName, string $value): void {
        # 1. If qualifiedName does not match the Name production in XML, then throw an
        #    "InvalidCharacterError" DOMException.
        if (preg_match(self::$nameProductionRegex, $qualifiedName) !== 1) {
            throw new DOMException(DOMException::INVALID_CHARACTER);
        }

        # 2. If this is in the HTML namespace and its node document is an HTML document,
        #    then set qualifiedName to qualifiedName in ASCII lowercase.
        // Document will always be an HTML document
        if ($this->isHTMLNamespace()) {
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
        // ClassList stuff because php garbage collection is... garbage.
        if ($qualifiedName === 'class' && $value === '') {
            ElementMap::delete($this);
        }
        try {
            parent::setAttributeNS(null, $qualifiedName, $value);
        } catch (\DOMException $e) {
            // The attribute name is invalid for XML
            // Replace any offending characters with "UHHHHHH" where H are the uppercase
            // hexadecimal digits of the character's code point
            parent::setAttributeNS(null, $this->coerceName($qualifiedName), $value);
        }

        // If you create an id attribute this way it won't be used by PHP in
        // getElementById, so let's fix that.
        if ($qualifiedName === 'id' && $namespaceURI === null) {
            $this->setIdAttribute($qualifiedName, true);
        }

        // ClassList stuff because php garbage collection is... garbage.
        if ($qualifiedName === 'class') {
            ElementMap::delete($this);
        }
        // If you create an id attribute this way it won't be used by PHP in
        // getElementById, so let's fix that.
        elseif ($qualifiedName === 'id') {
            $this->setIdAttribute($qualifiedName, true);
        }
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
        if ($namespace === Parser::XMLNS_NAMESPACE) {
            // NOTE: We create attribute nodes so that xmlns attributes
            // don't get lost; otherwise they cannot be serialized
            $a = @$this->ownerDocument->createAttributeNS($namespace, $qualifiedName);
            if ($a === false) {
                // The document element does not exist yet, so we need
                // to insert this element into the document
                $this->ownerDocument->appendChild($this);
                $a = $this->ownerDocument->createAttributeNS($namespace, $qualifiedName);
                $this->ownerDocument->removeChild($this);
            }
            $a->value = $this->escapeString($value, true);
            $this->setAttributeNodeNS($a);
        } else {
            try {
                parent::setAttributeNS($namespace, $qualifiedName, $value);
            } catch (\DOMException $e) {
                // The attribute name is invalid for XML
                // Replace any offending characters with "UHHHHHH" where H are the
                // uppercase hexadecimal digits of the character's code point
                if ($namespace !== null) {
                    $qualifiedName = implode(':', array_map([$this, 'coerceName'], explode(':', $qualifiedName, 2)));
                } else {
                    $qualifiedName = $this->coerceName($qualifiedName);
                }
                parent::setAttributeNS($namespace, $qualifiedName, $value);
            }
        }

        if ($namespace === null) {
            // ClassList stuff because php garbage collection is... garbage.
            if ($qualifiedName === 'class') {
                ElementMap::delete($this);
            }
            // If you create an id attribute this way it won't be used by PHP in
            // getElementById, so let's fix that.
            elseif ($qualifiedName === 'id') {
                $this->setIdAttribute($qualifiedName, true);
            }
        }
    }
}
