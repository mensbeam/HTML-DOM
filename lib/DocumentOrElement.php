<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\InnerNode\{
    Document as InnerDocument,
    Reflection
};
use MensBeam\HTML\Parser;
use MensBeam\HTML\Parser\Data;


/**
 * Not in standard. Exists so Document and Element can share some properties and
 * methods. For instance, getElementsByClassName is mentioned in the standard in
 * both the Document and Element interfaces.
 */
trait DocumentOrElement {
    public function getElementsByClassName(string $classNames): HTMLCollection {
        $innerNode = $this->innerNode;

        # The list of elements with class names classNames for a node root is the
        # HTMLCollection returned by the following algorithm:
        #
        # 1. Let classes be the result of running the ordered set parser on classNames.
        #
        ## The ordered set parser takes a string input and then runs these steps:
        ##
        ## 1. Let inputTokens be the result of splitting input on ASCII whitespace.
        // There isn't a Set object in php, so make sure all the tokens are unique.
        $inputTokens = ($classNames !== '') ? array_unique(preg_split(Data::WHITESPACE_REGEX, $classNames)) : [];

        if ($this instanceof Document) {
            $doc = $innerNode;
            $wrapperDoc = $this;
        } else {
            $doc = $innerNode->ownerDocument;
            $wrapperDoc = $this->ownerDocument;
        }

        ## 2. Let tokens be a new ordered set.
        ## 3. For each token in inputTokens, append token to tokens.
        ## 4. Return tokens.
        // There isn't a Set object in php, so just use the uniqued input tokens.

        # 2. If classes is the empty set, return an empty HTMLCollection.
        if ($inputTokens === []) {
            return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\HTMLCollection', $doc, new \DOMNodeList());
        }

        # 3. Return a HTMLCollection rooted at root, whose filter matches descendant
        # elements that have all their classes in classes.
        #
        # The comparisons for the classes must be done in an ASCII case-insensitive manner
        # if root’s node document’s mode is "quirks"; otherwise in an identical to manner.
        // It's just faster to use XPath to create the a nodelist that will then be
        // wrapped instead of polling a closure containing a DOM walker that has to then
        // explode each and every class string by whitespace and then iterate through
        // them... yeah not gonna do that.
        foreach ($inputTokens as &$token) {
            if ($wrapperDoc->compatMode === 'BackCompat') {
                $token = strtolower($token);
            }
            $token = "contains(concat(' ',normalize-space(@class),' '),' $token ')";
        }

        $query = './/*[' . implode(' and ', $inputTokens) . ']';

        return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\HTMLCollection', $doc, (new \DOMXPath($this->getInnerDocument()))->query($query, $innerNode));
    }

    public function getElementsByTagName(string $qualifiedName): HTMLCollection {
        // HTMLCollections cannot be created from their constructors normally.
        return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\HTMLCollection', (!$this instanceof Document) ? $this->innerNode->ownerDocument : $this->innerNode, $this->innerNode->getElementsByTagNameNS(null, $qualifiedName));
    }

    public function getElementsByTagNameNS(?string $namespace = null, string $localName): HTMLCollection {
        $doc = $this->getInnerDocument();
        if (!$doc instanceof XMLDocument) {
            if ($namespace === null) {
                return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\HTMLCollection', $doc, new \DOMNodeList());
            } elseif ($namespace === Parser::HTML_NAMESPACE) {
                $namespace = null;
            }
        }

        // If an HTML document and the namespace is the HTML namespace change it to null
        // before running internally because HTML nodes are stored with null namespaces
        // because of bugs in PHP DOM.
        if ($namespace === Parser::HTML_NAMESPACE && (($this instanceof Document && !$this instanceof XMLDocument) || !$this->ownerDocument instanceof XMLDocument)) {
            $namespace = null;
        }

        // HTMLCollections cannot be created from their constructors normally.
        return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\HTMLCollection', $doc, $this->innerNode->getElementsByTagNameNS($namespace, $localName));
    }


    protected function validateAndExtract(string $qualifiedName, ?string $namespace = null): array {
        # To validate and extract a namespace and qualifiedName, run these steps:
        # 1. If namespace is the empty string, set it to null.
        if ($namespace === '') {
            $namespace = null;
        }

        # 2. Validate qualifiedName.
        #    To validate a qualifiedName, throw an "InvalidCharacterError" DOMException if
        #    qualifiedName does not match the QName production.
        if (preg_match(InnerDocument::QNAME_PRODUCTION_REGEX, $qualifiedName) !== 1) {
            throw new DOMException(DOMException::INVALID_CHARACTER);
        }

        # 3. Let prefix be null.
        $prefix = null;

        # 4. Let localName be qualifiedName.
        $localName = $qualifiedName;

        # 5. If qualifiedName contains a ":" (U+003E), then split the string on it and
        #    set prefix to the part before and localName to the part after.
        if (strpos($qualifiedName, ':') !== false) {
            $temp = explode(':', $qualifiedName, 2);
            $prefix = $temp[0];
            $prefix = ($prefix !== '') ? $prefix : null;
            $localName = $temp[1];
        }

        #   6. If prefix is non-null and namespace is null, then throw a "NamespaceError" DOMException.
        #   7. If prefix is "xml" and namespace is not the XML namespace, then throw a "NamespaceError" DOMException.
        #   8. If either qualifiedName or prefix is "xmlns" and namespace is not the XMLNS
        #      namespace, then throw a "NamespaceError" DOMException.
        #   9. If namespace is the XMLNS namespace and neither qualifiedName nor prefix is
        #      "xmlns", then throw a "NamespaceError" DOMException.
        if (
            ($prefix !== null && $namespace === null) ||
            ($prefix === 'xml' && $namespace !== Parser::XML_NAMESPACE) ||
            (($qualifiedName === 'xmlns' || $prefix === 'xmlns') && $namespace !== Parser::XMLNS_NAMESPACE) ||
            ($namespace === Parser::XMLNS_NAMESPACE && $qualifiedName !== 'xmlns' && $prefix !== 'xmlns')
        ) {
            throw new DOMException(DOMException::NAMESPACE_ERROR);
        }

        # 10. Return namespace, prefix, and localName.
        return [
            // Internally HTML namespaced elements in HTML documents use null because of a PHP DOM bug.
            'namespace' => (!$this->getInnerDocument() instanceof XMLDocument && $namespace === Parser::HTML_NAMESPACE) ? null : $namespace,
            'prefix' => $prefix,
            'localName' => $localName
        ];
    }
}
