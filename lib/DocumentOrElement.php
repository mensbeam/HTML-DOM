<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\InnerNode\Document as InnerDocument,
    MensBeam\HTML\Parser;


/**
 * Not in standard. Exists so Document and Element can share some properties and
 * methods. For instance, getElementsByClassName is mentioned in the standard in
 * both the Document and Element interfaces.
 */
trait DocumentOrElement {
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
            'namespace' => $namespace,
            'prefix' => $prefix,
            'localName' => $localName
        ];
    }
}
