<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\DOMException\SyntaxError,
    MensBeam\HTML\DOM\Inner\Reflection;
use Symfony\Component\CssSelector\CssSelectorConverter,
    Symfony\Component\CssSelector\Exception\SyntaxErrorException as SymfonySyntaxErrorException;


trait ParentNode {
    protected function __get_childElementCount(): int {
        return $this->_innerNode->childElementCount;
    }

    protected function __get_children(): HTMLCollection {
        $doc = $this->getInnerDocument();
        // HTMLCollections cannot be created from their constructors normally.
        return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\HTMLCollection', $doc, $doc->xpath->query('.//*', $this->_innerNode));
    }

    protected function __get_firstElementChild(): ?Element {
        $result = $this->_innerNode->firstElementChild;
        return ($result !== null) ? $this->getInnerDocument()->getWrapperNode($result) : null;
    }

    protected function __get_lastElementChild(): ?Element {
        $result = $this->_innerNode->lastElementChild;
        return ($result !== null) ? $this->getInnerDocument()->getWrapperNode($result) : null;
    }


    public function append(Node|string ...$nodes): void {
        # The append(nodes) method steps are:
        # 1. Let node be the result of converting nodes into a node given nodes and this’s
        #    node document.
        $node = $this->convertNodesToNode($nodes);

        # 2. Append node to this.
        $this->appendChild($node);
    }

    public function prepend(Node|string ...$nodes): void {
        # The prepend(nodes) method steps are:
        # 1. Let node be the result of converting nodes into a node given nodes and this’s
        #    node document.
        $node = $this->convertNodesToNode($nodes);

        # 2. Pre-insert node into this before this’s first child.
        $this->insertBefore($node, $this->firstChild);
    }

    public function replaceChildren(Node|string ...$nodes): void {
        # The prepend(nodes) method steps are:
        # 1. Let node be the result of converting nodes into a node given nodes and this’s
        #    node document.
        $node = $this->convertNodesToNode($nodes);

        # 2. Ensure pre-insertion validity of node into this before null.
        $this->preInsertionValidity($node);

        # 3. Replace all with node within this.
        while ($this->_innerNode->hasChildNodes()) {
            $this->_innerNode->removeChild($this->_innerNode->firstChild);
        }

        $this->appendChild($node);
    }

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
            $node = $this->_innerNode->firstChild;
        }

        if ($node !== null) {
            $doc = $this->getInnerDocument();

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
                    default: throw new SyntaxError();
                }

                if ($node->parentNode !== null && $node->hasChildNodes()) {
                    yield from $wrapperNode->walk($filter);
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
                throw new SyntaxError();
            }

            return new \DOMNodeList;
        }

        # 3. Return the result of match a selector against a tree with s and node’s root
        #    using scoping root node. [SELECTORS4].
        $nodeList = $this->getInnerDocument()->xpath->query($s, $this->_innerNode);
        return $nodeList;
    }
}
