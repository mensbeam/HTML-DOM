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
use MensBeam\HTML\Parser,
    Symfony\Component\CssSelector\CssSelectorConverter,
    Symfony\Component\CssSelector\Exception\SyntaxErrorException as SymfonySyntaxErrorException;


class Element extends Node {
    use ChildNode, DocumentOrElement, ParentNode;

    protected function __get_attributes(): NamedNodeMap {
        // NamedNodeMaps cannot be created from their constructors normally.
        return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\NamedNodeMap', $this, ($this instanceof Document) ? $this->innerNode : $this->innerNode->ownerDocument, $this->innerNode->attributes);
    }

    protected function __get_classList(): DOMTokenList {
        # The classList getter steps are to return a DOMTokenList object whose
        # associated element is this and whose associated attribute’s local name is
        # class. The token set of this particular DOMTokenList object are also known as
        # the element’s classes.
        // DOMTokenLists cannot be created from their constructors normally.
        return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\DOMTokenList', $this, 'class');
    }

    protected function __get_className(): string {
        # The className attribute must reflect the "class" content attribute.
        # Return the result of running get an attribute value given this and name.
        return $this->getAttribute('class') ?? '';
    }

    protected function __set_className(string $value): void {
        # The className attribute must reflect the "class" content attribute.
        # Set an attribute value for this using name and the given value.
        $this->setAttribute('class', $value);
    }

    protected function __get_id(): string {
        # The id attribute must reflect the "id" content attribute.
        # Return the result of running get an attribute value given this and name.
        return $this->getAttribute('id') ?? '';
    }

    protected function __set_id(string $value): void {
        # The id attribute must reflect the "id" content attribute.
        # Set an attribute value for this using name and the given value.
        $this->setAttribute('id', $value);
    }

    protected function __get_innerHTML(): string {
        # On getting, return the result of invoking the fragment serializing algorithm
        # on the context object providing true for the require well-formed flag (this
        # might throw an exception instead of returning a string).
        // TODO: If adding better XML support this should require XML well-formed
        // serialization here.
        return Serializer::serializeInner($this->innerNode);
    }

    protected function __set_innerHTML(string $value): void {
        # On setting, these steps must be run:

        # 1. Let context element be the context object's host if the context object is a
        #    ShadowRoot object, or the context object otherwise.
        // There's no scripting in this implementation and therefore no shadow root
        // object.
        $context = $this;
        $innerContext = $this->innerNode;

        # 2. Let fragment be the result of invoking the fragment parsing algorithm with
        #    the new value as markup, and with context element.
        $innerFragment = Parser::parseFragment($innerContext, Parser::NO_QUIRKS_MODE, $value, 'UTF-8');
        $fragment = $innerContext->ownerDocument->getWrapperNode($innerFragment);
        $this->postParsingTemplatesFix($innerFragment);

        # 3. If the context object is a template element, then let context object be the
        #    template's template contents (a DocumentFragment).
        # NOTE: Setting innerHTML on a template element will replace all the nodes in
        #       its template contents (template.content) rather than its children.
        if ($this instanceof HTMLTemplateElement) {
            $context = $this->content;
            $innerContext = $this->getInnerNode($context);
        }

        # 4. Replace all with fragment within the context object.
        while ($innerContext->hasChildNodes()) {
            $innerContext->removeChild($innerContext->firstChild);
        }

        if (!$this instanceof HTMLTemplateElement) {
            $context->appendChild($fragment);
        } else {
            while ($innerFragment->hasChildNodes()) {
                $context->appendChild($fragment->firstChild);
            }
        }
    }

    protected function __get_localName(): ?string {
        // PHP's DOM does this correctly already.
        return $this->innerNode->localName;
    }

    protected function __get_namespaceURI(): ?string {
        // PHP's DOM uses null incorrectly for the HTML namespace, and if you attempt to
        // use the HTML namespace anyway it has additional bugs we don't have to work
        // around because of the wrapper classes; So, use the null namespace internally
        // but print out the HTML namespace instead.
        $namespace = $this->innerNode->namespaceURI;
        return (!$this->ownerDocument instanceof XMLDocument && $namespace === null) ? self::HTML_NAMESPACE : $namespace;
    }

    protected function __get_outerHTML(): string {
        # On getting, return the result of invoking the fragment serializing algorithm
        # on a fictional node whose only child is the context object providing true
        # for the require well-formed flag (this might throw an exception instead of
        # returning a string).
        // TODO: If adding better XML support this should require XML well-formed
        // serialization here.
        return Serializer::serialize($this->innerNode);
    }

    protected function __set_outerHTML(string $value): void {
        # On setting, the following steps must be run:
        #
        # 1. Let parent be the context object's parent.
        $parent = $this->parentNode;
        $innerParent = $this->innerNode->parentNode;

        # 2. If parent is null, terminate these steps. There would be no way to obtain a
        #    reference to the nodes created even if the remaining steps were run.
        if ($innerParent === null) {
            return;
        }

        # 3. If parent is a Document, throw a "NoModificationAllowedError" DOMException.
        if ($innerParent instanceof \DOMDocument) {
            throw new DOMException(DOMException::NO_MODIFICATION_ALLOWED);
        }

        # 4. If parent is a DocumentFragment, let parent be a new Element with:
        #       • body as its local name,
        #       • The HTML namespace as its namespace, and
        #       • The context object's node document as its node document.
        if ($innerParent instanceof \DOMDocumentFragment) {
            $innerParent = $this->innerNode->ownerDocument->createElement('body');
        }

        # 5. Let fragment be the result of invoking the fragment parsing algorithm with
        #    the new value as markup, and parent as the context element.
        $innerFragment = Parser::parseFragment($innerParent, Parser::NO_QUIRKS_MODE, $value, 'UTF-8');
        $fragment = $this->innerNode->ownerDocument->getWrapperNode($innerFragment);
        $this->postParsingTemplatesFix($innerFragment);

        # 6. Replace the context object with fragment within the context object's
        #    parent.
        $parent->replaceChild($fragment, $this);
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


    public function closest(string $selectors): ?Element {
        # The closest(selectors) method steps are:

        # 1. Let s be the result of parse a selector from selectors. [SELECTORS4]
        try {
            $converter = new CssSelectorConverter();
            $s = $converter->toXPath($selectors);
        } catch (\Exception $e) {
            # 2. If s is failure, throw a "SyntaxError" DOMException.
            if ($e instanceof SymfonySyntaxErrorException) {
                throw new DOMException(DOMException::SYNTAX_ERROR);
            }
        }

        # 3. Let elements be this’s inclusive ancestors that are elements, in reverse
        #    tree order.
        # 4. For each element in elements, if match a selector against an element,
        #    using s, element, and :scope element this, returns success, return element.
        #    [SELECTORS4]
        // Ideally we'd be able to wrap the XPath query in `ancestor-or-self::*[]`, but
        // the queries generated by Symfony's library aren't conducive to that kind of
        // query, so we must instead manually loop instead of having XPath do it for
        // us...
        $innerNode = $this->innerNode;
        $doc = $this->getInnerDocument();
        $xpath = $doc->xpath;
        $n = $innerNode;
        do {
            if (!$n instanceof \DOMElement) {
                break;
            }

            $result = $xpath->query($s, $n->parentNode);
            // If there is a match iterate through the results. If $this is one of them
            // return that, otherwise return the first item.
            if ($result->length > 0) {
                foreach ($result as $r) {
                    if ($r === $innerNode) {
                        return $this;
                    }
                }

                return $doc->getWrapperNode($result->item(0));
            }
        } while ($n = $n->parentNode);

        # 5. Return null.
        return null;
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
        return $attr->value;
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

    public function getAttributeNode(string $qualifiedName): ?Attr {
        # The getAttributeNode(qualifiedName) method steps are to return the result of
        # getting an attribute given qualifiedName and this.
        #
        # To get an attribute by name given a qualifiedName and element element, run
        # these steps:
        #
        # 1. If element is in the HTML namespace and its node document is an HTML document,
        #    then set qualifiedName to qualifiedName in ASCII lowercase.
        if (!$this instanceof XMLDocument && $this->namespaceURI === self::HTML_NAMESPACE) {
            $qualifiedName = strtolower($qualifiedName);
        }

        $qualifiedName = $this->coerceName($qualifiedName);

        # 2. Return the first attribute in element’s attribute list whose qualified name is
        #    qualifiedName; otherwise null.
        // Manually going through the attributes because PHP DOM has issues, returning
        // odd "DOMNamespaceNode" objects sometimes instead of the actual attributes...
        // yeah...
        $attributes = $this->innerNode->attributes;
        foreach ($attributes as $attr) {
            if ($attr->nodeName === $qualifiedName) {
                return $this->innerNode->ownerDocument->getWrapperNode($attr);
            }
        }

        return null;
    }

    public function getAttributeNodeNS(?string $namespace, string $localName): ?Attr {
        static $count = 0;
        $count++;
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

        $localName = $this->coerceName($localName);

        # 2. Return the attribute in element’s attribute list whose namespace is namespace
        #    and local name is localName, if any; otherwise null.
        // Manually going through the attributes because PHP DOM has issues, returning
        // odd "DOMNamespaceNode" objects sometimes instead of the actual attributes...
        // yeah...
        $attributes = $this->innerNode->attributes;
        foreach ($attributes as $attr) {
            if ($attr->namespaceURI === $namespace && $attr->localName === $localName) {
                return $this->innerNode->ownerDocument->getWrapperNode($attr);
            }
        }

        return null;
    }

    public function getAttributeNS(?string $namespace, string $localName): ?string {
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
        return $attr->value;
    }

    public function hasAttribute(string $qualifiedName): bool {
        # The hasAttribute(qualifiedName) method steps are:
        #
        # 1. If this is in the HTML namespace and its node document is an HTML document,
        #    then set qualifiedName to qualifiedName in ASCII lowercase.
        if (!$this->ownerDocument instanceof XMLDocument && $this->namespaceURI === self::HTML_NAMESPACE) {
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

    public function hasAttributeNS(?string $namespace, string $localName): bool {
        # The hasAttributeNS(namespace, localName) method steps are:
        #
        # 1. If namespace is the empty string, then set it to null.
        if ($namespace === '') {
            $namespace = null;
        }

        # 2. Return true if this has an attribute whose namespace is namespace and local
        #    name is localName; otherwise false.
        # An element has an attribute A if its attribute list contains A.
        // Going to try to handle this by getting the PHP DOM to do the heavy lifting
        // when we can because it's faster.
        $value = $this->innerNode->hasAttributeNS($namespace, $localName);
        if (!$value) {
            // PHP DOM does not acknowledge the presence of XMLNS-namespace attributes,
            // so try it again just in case; getAttributeNode will coerce names if
            // necessary, too.
            $value = ($this->getAttributeNodeNS($namespace, $localName) !== null);
        }

        return $value;
    }

    public function hasAttributes(): bool {
        # The hasAttributes() method steps are to return false if this’s attribute list
        # is empty; otherwise true.
        // PHP's DOM does this correctly already.
        return $this->innerNode->hasAttributes();
    }

    public function insertAdjacentElement(string $where, Element $element): ?Element {
        # The insertAdjacentElement(where, element) method steps are to return the
        # result of running insert adjacent, give this, where, and element.
        return $this->insertAdjacent($this, $where, $element);
    }

    public function insertAdjacentHTML(string $where, string $data): void {
        # The insertAdjacentHTML(position, text) method must run these steps:
        // This is from the W3C specification which the WHATWG delegates to concerning
        // innerHTML, outerHTML and this one. Using $where and $data for the parameters
        // to keep things consistent with the WHATWG specification.

        # 1. Use the first matching item from this list:
        $where = strtolower($where);
        switch ($where) {
            case 'beforebegin':
            case 'afterend':
                # Let context be the context object's parent.
                $context = $this->parentNode;
                $innerContext = $this->innerNode->parentNode;

                # If context is null or a Document, throw a "NoModificationAllowedError" DOMException.
                if ($context === null || $context instanceof Document) {
                    throw new DOMException(DOMException::NO_MODIFICATION_ALLOWED);
                }
            break;
            case 'afterbegin':
            case 'beforeend':
                # Let context be the context object.
                $context = $this;
                $innerContext = $this->innerNode;
            break;
            default: throw new DOMException(DOMException::SYNTAX_ERROR);
        }

        # 2. If context is not an Element or the following are all true:
        #       • context's node document is an HTML document,
        #       • context's local name is "html", and
        #       • context's namespace is the HTML namespace;
        if (!$context instanceof Element || ($context->ownerDocument instanceof Document && $context->localName === 'html' && $context->namespaceURI === Node::HTML_NAMESPACE)) {
            # let context be a new Element with
            #   • body as its local name,
            #   • The HTML namespace as its namespace, and
            #   • The context object's node document as its node document.
            $context = $context->ownerDocument->createElement('body');
            $innerContext = $this->getInnerNode($context);
        }

        # 3. Let fragment be the result of invoking the fragment parsing algorithm with
        #    text as markup, and context as the context element.
        $innerFragment = Parser::parseFragment($innerContext, Parser::NO_QUIRKS_MODE, $data, 'UTF-8');
        $fragment = $innerContext->ownerDocument->getWrapperNode($innerFragment);

        # 4. Use the first matching item from this list:
        switch ($where) {
            case 'beforebegin':
                # Insert fragment into the context object's parent before the context object.
                $this->parentNode->insertBefore($fragment, $this);
            break;

            case 'afterbegin':
                # Insert fragment into the context object before its first child.
                $this->insertBefore($fragment, $this->firstChild);
            break;

            case 'beforeend':
                # Append fragment to the context object.
                $this->appendChild($fragment);
            break;

            case 'afterend':
                # Insert fragment into the context object's parent before the context object's
                # next sibling.
                $this->parentNode->insertBefore($fragment, $this->nextSibling);
            break;
        }
    }

    public function insertAdjacentText(string $where, string $data): void {
        # The insertAdjacentText(where, data) method steps are:
        #
        # 1. Let text be a new Text node whose data is data and node document is this’s
        # node document.
        $text = $this->ownerDocument->createTextNode($data);

        # 2. Run insert adjacent, given this, where, and text.
        $this->insertAdjacent($this, $where, $text);
    }

    public function matches(string $selectors): bool {
        # The matches(selectors) and webkitMatchesSelector(selectors) method steps are:

        # 1. Let s be the result of parse a selector from selectors. [SELECTORS4]
        try {
            $converter = new CssSelectorConverter();
            $s = $converter->toXPath($selectors);
        } catch (\Exception $e) {
            # 2. If s is failure, throw a "SyntaxError" DOMException.
            if ($e instanceof SymfonySyntaxErrorException) {
                throw new DOMException(DOMException::SYNTAX_ERROR);
            }
        }

        # 3. If the result of match a selector against an element, using s, this, and
        #    :scope element this, returns success, then return true; otherwise, return
        #    false. [SELECTORS4]
        $innerNode = $this->innerNode;
        // Query the parent as the context node, yes. This is due to how the XPath
        // queries are generated from Symfony.
        return ($innerNode->ownerDocument->xpath->query($s, $innerNode->parentNode)->item(0) === $innerNode);
    }

    public function removeAttribute(string $qualifiedName): void {
        # The removeAttribute(qualifiedName) method steps are to remove an attribute
        # given qualifiedName and this, and then return undefined.
        #
        # To remove an attribute by name given a qualifiedName and element element, run
        # these steps:

        # 1. Let attr be the result of getting an attribute given qualifiedName and
        #    element.
        # 2. If attr is non-null, then remove attr.
        # 3. Return attr.
        // Going to let PHP's DOM do the heavy lifting here instead
        $this->innerNode->removeAttribute($this->coerceName($qualifiedName));
    }

    public function removeAttributeNode(Attr $attr): Attr {
        # The removeAttributeNode(attr) method steps are:
        # 1. If this’s attribute list does not contain attr, then throw a
        #    "NotFoundError" DOMException.
        // PHP's DOM does this already. Will catch its exception and rethrow as HTML-DOM
        // DOMException.

        # 2. Remove attr.
        try {
            $this->innerNode->removeAttributeNode(Reflection::getProtectedProperty($attr, 'innerNode'));
        } catch (\DOMException $e) {
            throw new DOMException($e->code);
        }

        # 3. Return attr.
        return $attr;
    }

    public function removeAttributeNS(?string $namespace, string $localName): void {
        # The removeAttributeNS(namespace, localName) method steps are to remove an
        # attribute given namespace, localName, and this, and then return undefined.
        #
        # To remove an attribute by namespace and local name given a namespace,
        # localName, and element element, run these steps:

        # 1. Let attr be the result of getting an attribute given namespace, localName,
        #    and element.
        # 2. If attr is non-null, then remove attr.
        # 3. Return attr.
        // Going to let PHP's DOM do the heavy lifting here instead
        $this->innerNode->removeAttributeNS($namespace, $this->coerceName($localName));
    }

    public function setAttribute(string $qualifiedName, string $value): void {
        # 1. If qualifiedName does not match the Name production in XML, then throw an
        #    "InvalidCharacterError" DOMException.
        if (!preg_match(InnerDocument::NAME_PRODUCTION_REGEX, $qualifiedName)) {
            throw new DOMException(DOMException::INVALID_CHARACTER);
        }

        # 2. If this is in the HTML namespace and its node document is an HTML document,
        #    then set qualifiedName to qualifiedName in ASCII lowercase.
        if (!$this->ownerDocument instanceof XMLDocument && $this->namespaceURI === Node::HTML_NAMESPACE) {
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
        # The setAttributeNode(attr) and setAttributeNodeNS(attr) methods steps are to
        # return the result of setting an attribute given attr and this.
        #
        # To set an attribute given an attr and element, run these steps:
        #
        # 1. If attr’s element is neither null nor element, throw an
        #    "InUseAttributeError" DOMException.
        $ownerElement = $attr->ownerElement;
        if ($ownerElement !== null && $ownerElement !== $this) {
            throw new DOMException(DOMException::IN_USE_ATTRIBUTE);
        }

        // PHP's DOM doesn't do this method correctly. It returns the old node if it is
        // replaced and null otherwise. It's always supposed to return a node.

        $oldAttr = $this->getAttributeNodeNS($attr->namespaceURI, $attr->localName);

        # 3. If oldAttr is attr, return attr.
        if ($oldAttr === $attr) {
            return $attr;
        }

        # 4. If oldAttr is non-null, then replace oldAttr with attr.
        if ($oldAttr !== null) {
            $this->innerNode->setAttributeNode($this->getInnerNode($attr));
            return $attr;
        }
        # 5. Otherwise, append attr to element.
        else {
            $this->innerNode->appendChild($this->getInnerNode($attr));
            return $attr;
        }

        # 6. Return oldAttr.
        return $oldAttr;
    }

    public function setAttributeNodeNS(Attr $attr): ?Attr {
        return $this->setAttributeNode($attr);
    }

    public function setAttributeNS(?string $namespace, string $qualifiedName, string $value): void {
        # 1. Let namespace, prefix, and localName be the result of passing namespace and
        #    qualifiedName to validate and extract.
        [ 'namespace' => $namespace, 'prefix' => $prefix, 'localName' => $localName ] = $this->validateAndExtract($qualifiedName, $namespace);

        $prefix = ($prefix === null) ? null : $this->coerceName($prefix);
        $localName = $this->coerceName($localName);
        $qualifiedName = ($prefix === null || $prefix === '') ? $localName : "{$prefix}:{$localName}";

        # 2. Set an attribute value for this using localName, value, and also prefix and
        #    namespace.
        // NOTE: We create attribute nodes so that xmlns attributes don't get lost;
        // otherwise they cannot be serialized
        if ($namespace === self::XMLNS_NAMESPACE) {
            $attr = $this->ownerDocument->createAttributeNS($namespace, $qualifiedName);
            $attr->value = $this->escapeString($value, true);
            $this->setAttributeNodeNS($attr);
        } else {
            $this->innerNode->setAttributeNS($namespace, $qualifiedName, $value);
        }

        // If you create an id attribute this way it won't be used by PHP in
        // getElementById, so let's fix that.
        if ($namespace === null && $qualifiedName === 'id') {
            $this->innerNode->setIdAttribute($qualifiedName, true);
        }
    }

    public function webkitMatchesSelector(string $selectors): bool {
        return $this->matches($selectors);
    }


    protected function insertAdjacent(Element $element, string $where, Node $node): ?Node {
        # To insert adjacent, given an element element, string where, and a node node,
        # run the steps associated with the first ASCII case-insensitive match for
        # where:
        switch (strtolower($where)) {
            case 'beforebegin':
                # If element’s parent is null, return null.
                if ($element->parentNode === null) {
                    return null;
                }

                # Return the result of pre-inserting node into element’s parent before element.
                return $element->parentNode->insertBefore($node, $element);
            break;

            case 'afterbegin':
                # Return the result of pre-inserting node into element before element’s first
                # child.
                return $element->insertBefore($node, $element->firstChild);
            break;

            case 'beforeend':
                # Return the result of pre-inserting node into element before null.
                // Isn't this just an appendChild?
                return $element->appendChild($node);
            break;

            case 'afterend':
                # If element’s parent is null, return null.
                if ($element->parentNode === null) {
                    return null;
                }

                # Return the result of pre-inserting node into element’s parent before element’s
                # next sibling.
                return $element->parentNode->insertBefore($node, $element->nextSibling);
            break;

            default: throw new DOMException(DOMException::SYNTAX_ERROR);
        }
    }
}