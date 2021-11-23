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
use Symfony\Component\CssSelector\CssSelectorConverter,
    Symfony\Component\CssSelector\Exception\SyntaxErrorException as SymfonySyntaxErrorException;


trait ParentNode {
    public function querySelector(string $selectors): ?Element {
        # The querySelector(selectors) method steps are to return the first result of
        # running scope-match a selectors string selectors against this, if the result
        # is not an empty list; otherwise null.
        $nodeList = $this->scopeMatchSelector($selectors);
        return ($nodeList->length > 0) ? $this->getInnerDocument()->getWrapperNode($nodeList[0]) : null;
    }

    public function querySelectorAll(string $selectors): NodeList {
        # The querySelectorAll(selectors) method steps are to return the static result
        # of running scope-match a selectors string selectors against this.
        $nodeList = $this->scopeMatchSelector($selectors);
        return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\NodeList', $this->getInnerDocument(), $nodeList);
    }

    /**
     * Generator which walks down the DOM from the node the method is being run on.
     * Non-standard.
     *
     * @param ?\Closure $filter An optional callback function used to filter; if not provided the generator will
     *                          just yield every node.
     * @param bool $includeReferenceNode An optional boolean flag which if true includes the reference node ($this) in
     *                                   the iteration.
     */
    public function walk(?\Closure $filter = null, bool $includeReferenceNode = false): \Generator {
        if ($this instanceof DocumentFragment || (!$this instanceof DocumentFragment && !$includeReferenceNode)) {
            $node = $node->firstChild;
        }

        if ($node !== null) {
            $doc = (!$node instanceof InnerDocument) ? $node->ownerDocument : $node;

            do {
                $next = $node->nextSibling;
                $wrapperNode = $doc->getWrapperNode($node);
                $result = ($filter === null) ? Node::WALK_ACCEPT : $filter($wrapperNode);

                switch ($result) {
                    case Node::WALK_ACCEPT:
                        yield $wrapperNode;
                    break;
                    case Node::WALK_ACCEPT | Node::WALK_SKIP_CHILDREN:
                        yield $wrapperNode;
                    case Node::WALK_REJECT | Node::WALK_SKIP_CHILDREN:
                    continue 2;
                    case Node::WALK_REJECT:
                    break;
                    default: return;
                }

                if ($node->parentNode !== null && $node->hasChildNodes()) {
                    yield from $node->walk($filter);
                }
            } while ($node = $next);
        }
    }


    protected function scopeMatchSelector(string $selectors): \DOMNodeList {
        # To scope-match a selectors string selectors against a node, run these steps:
        # 1. Let s be the result of parse a selector selectors. [SELECTORS4]
        // This implementation will instead convert the CSS selector to an XPath query
        // using Symfony's CSS selector converter library.
        try {
            $converter = new CssSelectorConverter();
            $s = $converter->toXPath($selectors);
        } catch (\Exception $e) {
            # 2. If s is failure, then throw a "SyntaxError" DOMException.
            // Symfony's library will throw an exception if something is unsupported, too,
            // so only throw exception when an actual syntax error, otherwise return an
            // empty nodelist.
            if ($e instanceof SymfonySyntaxErrorException) {
                throw new DOMException(DOMException::SYNTAX_ERROR);
            }

            return new \DOMNodeList;
        }

        # 3. Return the result of match a selector against a tree with s and node’s root
        #    using scoping root node. [SELECTORS4].
        $nodeList = $this->getInnerDocument()->xpath->query($s, $this->innerNode);
        return $nodeList;
    }

    protected function walkInner(\DOMNode $node, ?\Closure $filter = null, bool $includeReferenceNode = false): \Generator {
        if (!$node instanceof DocumentFragment && !$includeReferenceNode) {
            $node = $node->firstChild;
        }

        if ($node !== null) {
            $doc = (!$node instanceof InnerDocument) ? $node->ownerDocument : $node;

            do {
                $next = $node->nextSibling;
                $result = ($filter === null) ? Node::WALK_ACCEPT : $filter($node);

                switch ($result) {
                    case Node::WALK_ACCEPT:
                        yield $node;
                    break;
                    case Node::WALK_ACCEPT | Node::WALK_SKIP_CHILDREN:
                        yield $node;
                    case Node::WALK_REJECT | Node::WALK_SKIP_CHILDREN:
                    continue 2;
                    case Node::WALK_REJECT:
                    break;
                    default: return;
                }

                if ($node->parentNode !== null && $node->hasChildNodes()) {
                    yield from $this->walkInner($node, $filter);
                }
            } while ($node = $next);
        }
    }
}
