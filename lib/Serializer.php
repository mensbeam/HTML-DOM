<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\Inner\Reflection,
    MensBeam\HTML\Parser;
use MensBeam\HTML\Parser\{
    Config,
    Serializer as ParserSerializer
};


class Serializer extends ParserSerializer {
    protected static function getTemplateContent(\DOMElement $node): \DOMNode {
        return Reflection::getProtectedProperty($node->ownerDocument->getWrapperNode($node)->content, 'innerNode');
    }

    protected static function isPreformattedContent(\DOMNode $node): bool {
        $n = $node;
        do {
            if ($n instanceof \DOMElement) {
                if (($n->namespaceURI ?? Parser::HTML_NAMESPACE) === Parser::HTML_NAMESPACE && in_array($n->tagName, self::PREFORMATTED_ELEMENTS)) {
                    return true;
                }
            } elseif ($n instanceof \DOMDocumentFragment) {
                $host = Reflection::getProtectedProperty($node->ownerDocument->getWrapperNode($n), 'host');
                if ($host !== null) {
                    $n = Reflection::getProtectedProperty($host->get(), 'innerNode');
                }
            }
        } while ($n = $n->parentNode);

        return false;
    }

    protected static function treatAsBlockWithTemplates(\DOMNode $node): bool {
        $xpath = new \DOMXPath($node->ownerDocument);
        $templates = $xpath->query('.//template[namespace-uri()="" or namespace-uri()="http://www.w3.org/1999/xhtml"][not(ancestor::iframe[namespace-uri()="" or namespace-uri()="http://www.w3.org/1999/xhtml"] or ancestor::listing[namespace-uri()="" or namespace-uri()="http://www.w3.org/1999/xhtml"] or ancestor::noembed[namespace-uri()="" or namespace-uri()="http://www.w3.org/1999/xhtml"] or ancestor::noframes[namespace-uri()="" or namespace-uri()="http://www.w3.org/1999/xhtml"] or ancestor::noscript[namespace-uri()="" or namespace-uri()="http://www.w3.org/1999/xhtml"] or ancestor::plaintext[namespace-uri()="" or namespace-uri()="http://www.w3.org/1999/xhtml"] or ancestor::pre[namespace-uri()="" or namespace-uri()="http://www.w3.org/1999/xhtml"] or ancestor::style[namespace-uri()="" or namespace-uri()="http://www.w3.org/1999/xhtml"] or ancestor::script[namespace-uri()="" or namespace-uri()="http://www.w3.org/1999/xhtml"] or ancestor::textarea[namespace-uri()="" or namespace-uri()="http://www.w3.org/1999/xhtml"] or ancestor::title[namespace-uri()="" or namespace-uri()="http://www.w3.org/1999/xhtml"] or ancestor::xmp[namespace-uri()="" or namespace-uri()="http://www.w3.org/1999/xhtml"])]', $node);

        foreach ($templates as $t) {
            $content = Reflection::getProtectedProperty($t->ownerDocument->getWrapperNode($t)->content, 'innerNode');
            if ($xpath->evaluate(self::BLOCK_QUERY, $content) > 0) {
                return true;
            }
        }

        return false;
    }

    protected static function treatForeignRootAsBlock(\DOMNode $node): bool {
        // NOTE: This method is used only when pretty printing. Implementors of userland
        // PHP DOM solutions with template contents will need to extend this method to
        // be able to moonwalk through document fragment hosts.

        $n = $node;
        do {
            if ($n instanceof \DOMDocumentFragment) {
                $host = Reflection::getProtectedProperty($node->ownerDocument->getWrapperNode($n), 'host');
                if ($host !== null) {
                    $n = Reflection::getProtectedProperty($host->get(), 'innerNode');
                } else {
                    return true;
                }
            } else {
                if ($n->parentNode !== null && ($n->parentNode->namespaceURI ?? Parser::HTML_NAMESPACE) !== Parser::HTML_NAMESPACE) {
                    continue;
                }

                if (self::treatAsBlock($n->parentNode)) {
                    return true;
                }
            }

            break;
        } while ($n = $n->parentNode);

        return false;
    }
}
