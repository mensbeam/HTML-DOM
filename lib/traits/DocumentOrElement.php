<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\Parser,
    MensBeam\HTML\Parser\NameCoercion;


// This exists because the DOM spec for some stupid reason doesn't give
// DocumentFragment some methods.
trait DocumentOrElement {
    use NameCoercion;

    // Traits can't have constants, so statics are the next best thing
    // Regex used to validate names when creating elements and attributes.
    protected static string $nameProductionRegex = '/^[:A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}][:A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}-\.0-9\x{B7}\x{0300}-\x{036F}\x{203F}-\x{2040}]*$/Su';

    public function getElementsByClassName(string $classNames): \DOMNodeList {
        # The list of elements with class names classNames for a node root is the
        # HTMLCollection returned by the following algorithm:
        // DEVIATION: There's no HTMLCollection. The result will be a DOMNodeList
        // instead. It is, fortunately, almost exactly the same thing anyway.

        # 1. Let classes be the result of running the ordered set parser on classNames.
        #
        ## The ordered set parser takes a string input and then runs these steps:
        ##
        ## 1. Let inputTokens be the result of splitting input on ASCII whitespace.
        // There isn't a Set object in php, so make sure all the tokens are unique.
        $inputTokens = ($classNames !== '') ? array_unique(preg_split(Data::WHITESPACE_REGEX, $classNames)) : [];

        $isDocument = ($this instanceof Document);
        $document = ($isDocument) ? $this : $this->ownerDocument;

        ## 2. Let tokens be a new ordered set.
        ## 3. For each token in inputTokens, append token to tokens.
        ## 4. Return tokens.
        // There isn't a Set object in php, so just use the uniqued input tokens.

        # 2. If classes is the empty set, return an empty HTMLCollection.
        // DEVIATION: We can't do that, so let's create a bogus Xpath query instead.
        if ($inputTokens === []) {
            $ook = $document->createElement('ook');
            $query = $document->xpath->query('//eek', $ook);
            unset($ook);
            return $query;
        }

        # 3. Return a HTMLCollection rooted at root, whose filter matches descendant
        # elements that have all their classes in classes.
        #
        # The comparisons for the classes must be done in an ASCII case-insensitive manner
        # if root’s node document’s mode is "quirks"; otherwise in an identical to manner.
        // DEVIATION: Since we can't just create a \DOMNodeList we must instead query
        // the document with XPath with the root element to get a list.

        $query = '//*';
        foreach ($inputTokens as $token) {
            $query .= "[@class=\"$token\"]";
        }

        return ($isDocument) ? $document->xpath->query($query) : $document->xpath->query($query, $this);
    }


    protected function escapeString(string $string, bool $attribute = false): string {
        # Escaping a string (for the purposes of the algorithm above) consists of
        # running the following steps:

        # 1. Replace any occurrence of the "&" character by the string "&amp;".
        # 2. Replace any occurrences of the U+00A0 NO-BREAK SPACE character by the
        # string "&nbsp;".
        $string = str_replace(['&', "\u{A0}"], ['&amp;', '&nbsp;'], $string);
        # 3. If the algorithm was invoked in the attribute mode, replace any
        # occurrences of the """ character by the string "&quot;".
        # 4. If the algorithm was not invoked in the attribute mode, replace any
        # occurrences of the "<" character by the string "&lt;", and any
        # occurrences of the ">" character by the string "&gt;".
        return ($attribute) ? str_replace('"', '&quot;', $string) : str_replace(['<', '>'], ['&lt;', '&gt;'], $string);
    }

    protected function isHTMLNamespace(?\DOMNode $node = null): bool {
        $node = $node ?? $this;
        return ($node->namespaceURI === null || $node->namespaceURI === Parser::HTML_NAMESPACE);
    }

    protected function validateAndExtract(string $qualifiedName, ?string $namespace = null): array {
        static $qNameProductionRegex = '/^([A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}][A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}-\.0-9\x{B7}\x{0300}-\x{036F}\x{203F}-\x{2040}]*:)?[A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}][A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}-\.0-9\x{B7}\x{0300}-\x{036F}\x{203F}-\x{2040}]*$/Su';

        # To validate and extract a namespace and qualifiedName, run these steps:
        # 1. If namespace is the empty string, set it to null.
        if ($namespace === '') {
            $namespace = null;
        }

        # 2. Validate qualifiedName.
        #    To validate a qualifiedName, throw an "InvalidCharacterError" DOMException if
        #    qualifiedName does not match the QName production.
        if (preg_match($qNameProductionRegex, $qualifiedName) !== 1) {
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
            'namespace' => $namespace,
            'prefix' => $prefix,
            'localName' => $localName
        ];
    }
}