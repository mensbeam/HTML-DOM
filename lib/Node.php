<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\Framework\MagicProperties,
    MensBeam\HTML\DOM\InnerNode\Reflection;


abstract class Node {
    use MagicProperties;

    public const ELEMENT_NODE = 1;
    public const ATTRIBUTE_NODE = 2;
    public const TEXT_NODE = 3;
    public const CDATA_SECTION_NODE = 4;
    public const ENTITY_REFERENCE_NODE = 5; // legacy
    public const ENTITY_NODE = 6; // legacy
    public const PROCESSING_INSTRUCTION_NODE = 7;
    public const COMMENT_NODE = 8;
    public const DOCUMENT_MODE = 9;
    public const DOCUMENT_TYPE_NODE = 10;
    public const DOCUMENT_FRAGMENT_NODE = 11;
    public const NOTATION_NODE = 12; // legacy

    public const DOCUMENT_POSITION_DISCONNECTED = 0x01;
    public const DOCUMENT_POSITION_PRECEDING = 0x02;
    public const DOCUMENT_POSITION_FOLLOWING = 0x04;
    public const DOCUMENT_POSITION_CONTAINS = 0x08;
    public const DOCUMENT_POSITION_CONTAINED_BY = 0x10;
    public const DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC = 0x20;

    protected ?NodeList $_childNodes = null;
    protected \DOMNode $innerNode;

    private static ?int $rand = null;

    protected function __get_baseURI(): string {
        # The baseURI getter steps are to return this’s node document’s document base
        # URL, serialized.
        #
        # The document base URL of a Document object is the absolute URL obtained by running these steps:
        $document = ($this instanceof Document) ? $this : $this->ownerDocument;
        $base = $doc->getElementsByNodeName('base');
        foreach ($base as $b) {
            $href = $base->getAttribute('href');
            # 2. Otherwise, return the frozen base URL of the first base element in the
            #    Document that has an href attribute, in tree order.
            // URL of base element is always frozen
            if ($href !== null) {
                return $href;
            }
        }

        # 1. If there is no base element that has an href attribute in the Document,
        #    then return the Document's fallback base URL.
        // This is going to be done last because I have to iterate over the base elements first.
        # The fallback base URL of a Document object document is the URL record obtained by running these steps:
        #
        # 1. If document is an iframe srcdoc document, then return the document base URL
        #    of document's browsing context's container document.
        // DEVIATION: There can't be an iframe srcdoc document in this implementation.
        # 2. If document's URL is about:blank, and document's browsing context's creator
        #    base URL is non-null, then return that creator base URL.
        // DEVIATION: Document's URL cannot be about:blank in this implementation.
        # 3. Return document's URL.
        return $document->URL;
    }

    protected function __get_childNodes(): NodeList {
        // NodeLists cannot be created from their constructors normally.
        // OPTIMIZATION: Going to optimize here and only create a truly live NodeList if
        // the node is even capable of having children, otherwise will just be an empty
        // NodeList. There is no sense in generating a live list that will never update.
        if ($this instanceof Document || $this instanceof DocumentFragment || $this instanceof Element) {
            $doc = ($this instanceof Document) ? $this->innerNode : $this->innerNode->ownerDocument;
            return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\NodeList', function() use($doc) {
                $result = [];
                $innerChildNodes = $this->innerNode->childNodes;
                foreach ($innerChildNodes as $i) {
                    $result[] = $doc->getWrapperNode($i);
                }

                return $result;
            });
        }

        if ($this->_childNodes !== null) {
            return $this->_childNodes;
        }

        $this->_childNodes = Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\Nodelist', []);
        return $this->_childNodes;
    }

    protected function __get_firstChild(): ?Node {
        // PHP's DOM does this correctly already.
        $doc = ($this instanceof Document) ? $this->innerNode : $this->innerNode->ownerDocument;
        return $doc->getWrapperNode($this->innerNode->firstChild);
    }

    protected function __get_isConnected(): bool {
        # The isConnected getter steps are to return true, if this is connected;
        # otherwise false.
        # An element is connected if its shadow-including root is a document.
        return ($this->getRootNode() instanceof Document);
    }

    protected function __get_lastChild(): ?Node {
        // PHP's DOM does this correctly already.
        return $this->innerNode->ownerDocument->getWrapperNode($this->innerNode->lastChild);
    }

    protected function __get_previousSibling(): ?Node {
        // PHP's DOM does this correctly already.
        return $this->innerNode->ownerDocument->getWrapperNode($this->innerNode->previousSibling);
    }

    protected function __get_nextSibling(): ?Node {
        // PHP's DOM does this correctly already.
        return $this->innerNode->ownerDocument->getWrapperNode($this->innerNode->nextSibling);
    }

    protected function __get_nodeName(): string {
        # The nodeName getter steps are to return the first matching statement,
        # switching on the interface this implements:

        # ↪ Element
        #     Its HTML-uppercased qualified name.
        if ($this instanceof Element) {
            return strtoupper($this->innerNode->nodeName);
        }

        // PHP's DOM mostly does this correctly with the exception of Element, so let's
        // fall back to PHP's DOM on everything else.
        return $this->innerNode->nodeName;
    }

    protected function __get_nodeType(): int {
        // PHP's DOM does this correctly already.
        return $this->innerNode->nodeType;
    }

    protected function __get_nodeValue(): ?string {
        # The nodeValue getter steps are to return the following, switching on the
        # interface this implements:

        # ↪ Otherwise
        #     Null.
        if ($this instanceof Element) {
            return null;
        }

        // PHP's DOM mostly does this correctly with the exception of Element, so let's
        // fall back to PHP's DOM on everything else.
        return $this->innerNode->nodeValue;
    }

    protected function __set_nodeValue(?string $value) {
        # The nodeValue setter steps are to, if the given value is null, act as if it
        # was the empty string instead, and then do as described below, switching on the
        # interface this implements:

        # ↪ Otherwise
        #     Do nothing.
        if ($this instanceof Element) {
            return;
        }

        // PHP's DOM mostly does this correctly with the exception of Element, so let's
        // fall back to PHP's DOM on everything else.
        $this->innerNode->nodeValue = $value;
    }

    protected function __get_ownerDocument(): Document {
        # The ownerDocument getter steps are to return null, if this is a document;
        # otherwise this’s node document.
        // PHP's DOM does this correctly already.
        if ($this instanceof Document) {
            return null;
        }

        return $this->innerNode->ownerDocument->getWrapperNode($this->innerNode->ownerDocument);
    }

    protected function __get_parentElement(): ?Element {
        # The parentElement getter steps are to return this’s parent element.
        # A node’s parent of type Element is known as its parent element. If the node
        # has a parent of a different type, its parent element is null.
        $parent = $this->parentNode;
        return ($parent instanceof Element) ? $parent : null;
    }

    protected function __get_parentNode(): ?Node {
        # The parentNode getter steps are to return this’s parent.
        # An object that participates in a tree has a parent, which is either null or an
        # object, and has children, which is an ordered set of objects. An object A
        # whose parent is object B is a child of B.
        if ($this instanceof Document) {
            return null;
        }

        $parent = $this->innerNode->parentNode;
        if ($parent === null) {
            return null;
        }

        return $parent->ownerDocument->getWrapperNode($parent);
    }

    protected function __get_textContent(): string {
        // PHP's DOM does this correctly already.
        return $this->innerNode->textContent;
    }


    protected function __construct(\DOMNode $innerNode) {
        $this->innerNode = $innerNode;
    }


    public function appendChild(Node $node): Node {
        # The appendChild(node) method steps are to return the result of appending node to
        # this.
        // Aside from pre-insertion validity PHP's DOM does this correctly already.
        $this->preInsertionValidity($node);
        $this->innerNode->appendChild($this->getInnerNode($node));
        return $node;
    }

    public function cloneNode(?bool $deep = false): Node {
        // PHP's DOM does this correctly already.
        $newInner = $this->innerNode->cloneNode($deep);
        return $newInner->ownerDocument->getWrapperNode($newInner);
    }

    public function compareDocumentPosition(Node $other): int {
        # The compareDocumentPosition(other) method steps are:
        #
        # 1. If this is other, then return zero.
        if ($this === $other) {
            return 0;
        }

        # 2. Let node1 be other and node2 be this.
        $node1 = $other;
        $node2 = $this;

        # 3. Let attr1 and attr2 be null.
        $attr1 = $attr2 = null;

        # 4. If node1 is an attribute, then set attr1 to node1 and node1 to attr1’s
        #   element.
        if ($node1 instanceof Attr) {
            $attr1 = $node1;
            $node1 = $attr1->ownerElement;
        }

        # 5. If node2 is an attribute, then:
        if ($node2 instanceof Attr) {
            # 1. Set attr2 to node2 and node2 to attr2’s element.
            $attr2 = $node2;
            $node2 = $attr2->ownerElement;

            # 2. If attr1 and node1 are non-null, and node2 is node1, then:
            if ($attr1 !== null && $node1 !== null && $node2 === $node1) {
                # 1. For each attr in node2’s attribute list:
                foreach ($node2->attributes as $attr) {
                    # 1. If attr equals attr1, then return the result of adding DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC and DOCUMENT_POSITION_PRECEDING.
                    if ($attr === $attr1) {
                        return Node::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC + Node::DOCUMENT_POSITION_PRECEDING;
                    }

                    # 2. If attr equals attr2, then return the result of adding DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC and DOCUMENT_POSITION_FOLLOWING.
                    if ($attr === $attr2) {
                        return Node::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC + Node::DOCUMENT_POSITION_FOLLOWING;
                    }
                }
            }
        }

        # 6. If node1 or node2 is null, or node1’s root is not node2’s root, then return the
        #    result of adding DOCUMENT_POSITION_DISCONNECTED,
        #    DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC, and either
        #    DOCUMENT_POSITION_PRECEDING or DOCUMENT_POSITION_FOLLOWING, with the constraint
        #    that this is to be consistent, together.
        #
        # NOTE: Whether to return DOCUMENT_POSITION_PRECEDING or
        # DOCUMENT_POSITION_FOLLOWING is typically implemented via pointer comparison.
        # In JavaScript implementations a cached Math.random() value can be used.
        if (self::$rand === null) {
            self::$rand = rand(0, 1);
        }

        if ($node1 === null || $node2 === null || $node1->getRootNode() !== $node2->getRootNode()) {
            return Node::DOCUMENT_POSITION_DISCONNECTED + Node::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC + ((self::$rand === 0) ? Node::DOCUMENT_POSITION_PRECEDING : Node::DOCUMENT_POSITION_FOLLOWING);
        }

        # 7. If node1 is an ancestor of node2 and attr1 is null, or node1 is node2 and attr2
        #    is non-null, then return the result of adding DOCUMENT_POSITION_CONTAINS to
        #    DOCUMENT_POSITION_PRECEDING.
        if (($node1 === $node2 && $attr2 !== null) || ($attr1 === null && $node2->contains($node1))) {
            return Node::DOCUMENT_POSITION_CONTAINS + Node::DOCUMENT_POSITION_PRECEDING;
        }

        # 8. If node1 is a descendant of node2 and attr2 is null, or node1 is node2 and attr1
        #    is non-null, then return the result of adding DOCUMENT_POSITION_CONTAINED_BY to
        #    DOCUMENT_POSITION_FOLLOWING.
        if (($node1 === $node2 && $attr1 !== null) || ($attr2 === null && $node2->contains($node1))) {
            return Node::DOCUMENT_POSITION_CONTAINED_BY + Node::DOCUMENT_POSITION_FOLLOWING;
        }

        # 9. If node1 is preceding node2, then return DOCUMENT_POSITION_PRECEDING.
        if ($node2->walkPreceding(function($n) use($node1) {
            return ($n === $node1);
        })->current() !== null) {
            return Node::DOCUMENT_POSITION_PRECEDING;
        }

        # 10. Return DOCUMENT_POSITION_FOLLOWING.
        return Node::DOCUMENT_POSITION_FOLLOWING;
    }

    public function contains(?Node $other): bool {
        # The contains(other) method steps are to return true if other is an inclusive
        # descendant of this; otherwise false (including when other is null).
        return ($other->moonWalk(function($n) use($other) {
            return ($n === $other);
        })->current() !== null);
    }

    public function getRootNode(array $options = []): Node {
        # The getRootNode(options) method steps are to return this’s shadow-including
        # root if options["composed"] is true; otherwise this’s root.
        // DEVIATION: This implementation does not have scripting, so there's no Shadow
        // DOM. Therefore, there isn't a need for the options parameter.

        # The root of an object is itself, if its parent is null, or else it is the root
        # of its parent. The root of a tree is any object participating in that tree
        # whose parent is null.
        if ($this->parentNode === null) {
            return $this;
        }

        return $this->moonwalk(function($n) {
            return ($n->parentNode === null);
        })->current();
    }

    public function hasChildNodes(): bool {
        // PHP's DOM does this correctly already.
        return $this->innerNode->hasChildNodes();
    }

    public function insertBefore(Node $node, ?Node $child): Node {
        # The insertBefore(node, child) method steps are to return the result of
        # pre-inserting node into this before child.
        // Aside from pre-insertion validity PHP's DOM does this correctly already.
        $this->preInsertionValidity($node, $child);
        $this->innerNode->insertBefore($this->getInnerNode($node));
        return $node;
    }

    public function isDefaultNamespace(?string $namespace = null): bool {
        # The isDefaultNamespace(namespace) method steps are:
        // PHP DOM's implementation of this is broken for HTML, so let's do this
        // manually.

        # 1. If namespace is the empty string, then set it to null.
        if ($namespace === '') {
            $namespace = null;
        }

        # 2. Let defaultNamespace be the result of running locate a namespace for this
        #    using null.
        # 3. Return true if defaultNamespace is the same as namespace; otherwise false.
        return ($this->locateNamespace($this, null) === $namespace);
    }

    public function isEqualNode(?Node $otherNode) {
        # The isEqualNode(otherNode) method steps are to return true if otherNode is
        # non-null and this equals otherNode; otherwise false.

        # A node A equals a node B if all of the following conditions are true:
        #
        # • A and B implement the same interfaces.
        if ($this::class !== $otherNode::class) {
            return false;
        }

        # • The following are equal, switching on the interface A implements:
        #
        # ↪ DocumentType
        #      Its name, public ID, and system ID.
        if ($this instanceof DocumentType) {
            if ($this->name !== $otherNode->name || $this->publicId !== $otherNode->publicId || $this->systemId !== $this->publicId) {
                return false;
            }
        }
        # ↪ Element
        #      Its namespace, namespace prefix, local name, and its attribute list’s size.
        elseif ($this instanceof Element) {
            if ($this->namespaceURI !== $otherNode->namespaceURI || $this->prefix !== $otherNode->prefix || $this->localName !== $otherNode->localName || $this->attributes->length !== $otherNode->attributes->length) {
                return false;
            }

            # • If A is an element, each attribute in its attribute list has an attribute that
            #   equals an attribute in B’s attribute list.
            foreach ($this->attributes as $key => $attr) {
                if (!$attr->isEqualNode($otherNode->attributes[$key])) {
                    return false;
                }
            }
        }
        # ↪ Attr
        #       Its namespace, local name, and value.
        elseif ($this instanceof Attr) {
            if ($this->namespaceURI !== $otherNode->namespaceURI || $this->localName !== $otherNode->localName || $this->value !== $otherNode->value) {
                return false;
            }
        }
        # ↪ Text
        # ↪ Comment
        #      Its data.
        elseif ($this instanceof Text || $this instanceof Comment) {
            if ($this->data !== $otherNode->data) {
                return false;
            }
        }

        if ($this instanceof Document || $this instanceof DocumentFragment || $this instanceof Element) {
            # • A and B have the same number of children.
            if ($this->childNodes->length !== $otherNode->childNodes->length) {
                return false;
            }

            # • Each child of A equals the child of B at the identical index.
            foreach ($this->childNodes as $key => $child) {
                $other = $otherNode->childNodes[$key];
                if ($child->name !== $other->name || $child->publicId !== $other->publicId || $child->systemId !== $other->systemId) {
                    return false;
                }
            }
        }

        return true;
    }

    public function isSameNode(?Node $otherNode) {
        # The isSameNode(otherNode) method steps are to return true if otherNode is
        # this; otherwise false.
        return ($otherNode === $this);
    }

    public function lookupPrefix(?string $namespace = null): ?string {
        # The lookupPrefix(namespace) method steps are:
        // PHP DOM's implementation of this is broken for HTML, so let's do this
        // manually.

        # 1. If namespace is null or the empty string, then return null.
        if ($namespace === null || $namespace === '') {
            return null;
        }

        # 2. Switch on the interface this implements:
        #
        # ↪ Element
        if ($this instanceof Element) {
            # Return the result of locating a namespace prefix for it using namespace.
            return $this->locateNamespacePrefix($this, $namespace);
        }

        # ↪ Document
        elseif ($this instanceof Document) {
            # Return the result of locating a namespace prefix for its document element, if
            # its document element is non-null; otherwise null.
            return ($this->documentElement !== null) ? $this->locateNamespacePrefix($this->documentElement, $namespace) : null;
        }

        # ↪ DocumentType
        # ↪ DocumentFragment
        elseif ($this instanceof DocumentType || $this instanceof DocumentFragment) {
            return null;
        }

        # ↪ Attr
        elseif ($this instanceof Attr) {
            # Return the result of locating a namespace prefix for its element, if its
            # element is non-null; otherwise null.
            return $this->locateNamespacePrefix($this->ownerElement, $namespace);
        }

        # ↪ Otherwise
        #      Return the result of locating a namespace prefix for its parent element,
        #      if its parent element is non-null; otherwise null.
        $parentElement = $this->parentElement;
        return ($parentElement !== null) ? $this->locateNamespacePrefix($this->parentElement, $namespace) : null;
    }

    public function lookupNamespaceURI(?string $prefix = null): ?string {
        # The lookupNamespaceURI(prefix) method steps are:
        // PHP DOM's implementation of this is broken for HTML, so let's do this
        // manually.

        # 1. If prefix is the empty string, then set it to null.
        if ($prefix === '') {
            $prefix = null;
        }

        # 2. Return the result of running locate a namespace for this using prefix.
        return $this->locateNamespace($this, $prefix);
    }

    public function normalize(): void {
        // PHP's DOM does this correctly already.
        $this->innerNode->normalize();
    }

    public function removeChild(Node $child): Node {
        // PHP's DOM does this correctly already.
        $this->innerNode->removeChild($this->getInnerNode($child));
        return $node;
    }

    public function replaceChild(Node $node, Node $child): Node {
        # The replaceChild(node, child) method steps are to return the result of
        # replacing child with node within this.
        // PHP's DOM has some issues due to not checking for some edge cases the DOM
        // spec outlines for Node::replaceChild, so let's follow those before using the
        // PHP DOM to replace.

        # To replace a child with node within a parent, run these steps:
        #
        # 1. If parent is not a Document, DocumentFragment, or Element node, then throw
        #    a "HierarchyRequestError" DOMException.
        if (!$this instanceof Document && !$this instanceof DocumentFragment && !$this instanceof Element) {
            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
        }

        # 2. If node is a host-including inclusive ancestor of parent, then throw a
        #    "HierarchyRequestError" DOMException.
        if ($node->contains($this)) {
            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
        }

        # 3. If child’s parent is not parent, then throw a "NotFoundError" DOMException.
        if ($child->parentNode !== $this) {
            throw new DOMException(DOMException::NOT_FOUND);
        }

        # 4. If node is not a DocumentFragment, DocumentType, Element, or CharacterData
        #    node, then throw a "HierarchyRequestError" DOMException.
        if (!$node instanceof DocumentFragment && !$node instanceof DocumentType && !$node instanceof Element && !$node instanceof CharacterData) {
            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
        }

        # 5. If either node is a Text node and parent is a document, or node is a
        #    doctype and parent is not a document, then throw a "HierarchyRequestError"
        #    DOMException.
        if (($node instanceof Text && $this instanceof Document) || ($node instanceof DocumentType && !$this instanceof Document)) {
            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
        }

        # 6. If parent is a document, and any of the statements below, switched on the
        #    interface node implements, are true, then throw a "HierarchyRequestError".
        if ($this instanceof Document) {
            # ↪ DocumentFragment
            #      If node has more than one element child or has a Text node child.
            #
            #      Otherwise, if node has one element child and either parent has an element
            #      child that is not child or a doctype is following child.
            if ($node instanceof DocumentFragment) {
                $nodeChildElementCount = $node->childElementCount;
                if ($nodeChildElementCount > 1 || $node->firstChild->walkFollowing(function($n) {
                    return ($n instanceof Text);
                }, true)->current() !== null) {
                    throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                } elseif ($nodeChildElementCount === 1) {
                    $beforeChild = true;
                    if ($node->firstChild->walkFollowing(function($n) use(&$beforeChild, $child) {
                        if (!$beforeChild && $n instanceof DocumentType) {
                            return true;
                        }

                        if ($n instanceof Element && $n !== $child) {
                            return true;
                        } elseif ($n === $child) {
                            $beforeChild = false;
                        }

                        return false;
                    }, true)->current() !== null) {
                        throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                    }
                }
            }

            # ↪ Element
            #      parent has an element child that is not child or a doctype is following
            #      child.
            elseif ($node instanceof Element) {
                $beforeChild = true;
                if ($node->firstChild->walkFollowing(function($n) use(&$beforeChild, $child) {
                    if (!$beforeChild && $n instanceof DocumentType) {
                        return true;
                    }

                    if ($n instanceof Element && $n !== $child) {
                        return true;
                    } elseif ($n === $child) {
                        $beforeChild = false;
                    }

                    return false;
                }, true)->current() !== null) {
                    throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                }
            }

            # ↪ DocumentType
            #      parent has a doctype child that is not child, or an element is preceding
            #      child.
            elseif ($node instanceof DocumentType) {
                $beforeChild = true;
                if ($node->firstChild->walkFollowing(function($n) use(&$beforeChild, $child) {
                    if ($beforeChild && $n instanceof Element) {
                        return true;
                    }

                    if ($n instanceof DocumentType && $n !== $child) {
                        return true;
                    } elseif ($n === $child) {
                        $beforeChild = false;
                    }

                    return false;
                }, true)->current() !== null) {
                    throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                }
            }
        }

        // PHP's DOM does fine with the rest of the steps.
        $this->innerNode->replaceChild($this->getInnerNode($node), $this->getInnerNode($child));
        return $node;
    }


    protected function getInnerNode(?Node $node = null): \DOMNode {
        if ($node === null) {
            return $this->innerNode;
        }

        return Reflection::getProtectedProperty($node, 'innerNode');
    }

    protected function locateNamespace(Node $node, ?string $prefix = null): ?string {
        # To locate a namespace for a node using prefix, switch on the interface node
        # implements:
        #
        # ↪ Element
        if ($node instanceof Element) {
            # 1. If its namespace is non-null and its namespace prefix is prefix, then return
            #    namespace.
            if ($node->namespaceURI !== null && $node->prefix === $prefix) {
                return $node->namespaceURI;
            }

            # 2. If it has an attribute whose namespace is the XMLNS namespace, namespace prefix
            #    is "xmlns", and local name is prefix, or if prefix is null and it has an
            #    attribute whose namespace is the XMLNS namespace, namespace prefix is null, and
            #    local name is "xmlns", then return its value if it is not the empty string, and
            #    null otherwise.
            $attributes = $node->attributes;
            foreach ($attributes as $attr) {
                if (($attr->namespaceURI === Parser::XMLNS_NAMESPACE && $attr->prefix === 'xmlns' && $attr->localName === $prefix) || ($prefix === null && $attr->namespaceURI === Parser::XMLNS_NAMESPACE && $attr->prefix === null && $attr->localName === 'xmlns')) {
                    return ($attr->value !== '') ? $attr->value : null;
                }
            }

            $parentElement = $node->parentElement;

            # 3. If its parent element is null, then return null.
            if ($parentElement === null) {
                return null;
            }

            # 4. Return the result of running locate a namespace on its parent element using
            #    prefix.
            return $this->locateNamespace($parentElement, $prefix);
        }

        # ↪ Document
        elseif ($node instanceof Document) {
            # 1. If its document element is null, then return null.
            if ($node->documentElement === null) {
                return null;
            }

            # 2. Return the result of running locate a namespace on its document element
            #    using prefix.
            return $this->locateNamespace($node->documentElement, $prefix);
        }

        # ↪ DocumentType
        # ↪ DocumentFragment
        elseif ($node instanceof DocumentType || $node instanceof DocumentFragment) {
            # Return null.
            return null;
        }

        # ↪ Attr
        elseif ($node instanceof Attr) {
            # 1. If its element is null, then return null.
            if ($node->ownerElement === null) {
                return null;
            }

            # 2. Return the result of running locate a namespace on its element using
            #    prefix.
            return $this->locateNamespace($node->ownerElement, $prefix);
        }

        # ↪ Otherwise
        # 1. If its parent element is null, then return null.
        $parentElement = $node->parentElement;
        if ($parentElement === null) {
            return null;
        }

        # 2. Return the result of running locate a namespace on its parent element using
        #    prefix.
        return $this->locateNamespace($parentElement, $prefix);
    }

    protected function locateNamespacePrefix(Element $element, ?string $namespace = null) {
        # To locate a namespace prefix for an element using namespace, run these steps:
        #
        # 1. If element’s namespace is namespace and its namespace prefix is non-null,
        #    then return its namespace prefix.
        if ($element->namespaceURI === $namespace && $element->$prefix !== null) {
            return $element->prefix;
        }

        # 2. If element has an attribute whose namespace prefix is "xmlns" and value is
        #    namespace, then return element’s first such attribute’s local name.
        $attributes = $element->attributes;
        foreach ($attributes as $attr) {
            if ($attr->prefix === 'xmlns' && $attr->value === $namespace) {
                return $attr->localName;
            }
        }

        # 3. If element’s parent element is not null, then return the result of running
        #    locate a namespace prefix on that element using namespace.
        $parentElement = $element->parentElement;
        if ($parentElement !== null) {
            return $this->locateNamespacePrefix($parentElement, $namespace);
        }

        # Return null.
        return null;
    }

    protected function preInsertionValidity(Node $node, ?Node $child = null) {
        // "parent" in the spec comments below is $this

        # 1. If parent is not a Document, DocumentFragment, or Element node, then throw
        #    a "HierarchyRequestError" Exception.
        if (!$this instanceof Document && !$this instanceof DocumentFragment && !$this instanceof Element) {
            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
        }

        # 2. If node is a host-including inclusive ancestor of parent, then throw a
        #    "HierarchyRequestError" Exception.
        #
        # An object A is a host-including inclusive ancestor of an object B, if either
        # A is an inclusive ancestor of B, or if B’s root has a non-null host and A is a
        # host-including inclusive ancestor of B’s root’s host.
        if ($node->parentNode !== null) {
            if ($this->parentNode !== null && ($this === $node || $node->contains($this))) {
                throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
            } else {
                $parentRoot = $this->getRootNode();
                if ($parentRoot instanceof DocumentFragment) {
                    $parentRootHost = Reflection::getProtectedProperty($parentRoot, 'host')->get();
                    if ($parentRootHost !== null && ($parentRootHost === $node || $node->contains($parentRootHost))) {
                        throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                    }
                }
            }
        }

        # 3. If child is non-null and its parent is not parent, then throw a
        #    "NotFoundError" Exception.
        if ($child !== null && ($child->parentNode === null || $child->parentNode !== $this)) {
            throw new DOMException(DOMException::NOT_FOUND);
        }

        # 4. If node is not a DocumentFragment, DocumentType, Element, Text,
        #    ProcessingInstruction, or Comment node, then throw a "HierarchyRequestError"
        #    Exception.
        if (!$node instanceof DocumentFragment && !$node instanceof DocumentType && !$node instanceof Element && !$node instanceof Text && !$node instanceof ProcessingInstruction && !$node instanceof Comment) {
            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
        }

        # 5. If either node is a Text node and parent is a document, or node is a
        #    doctype and parent is not a document, then throw a "HierarchyRequestError"
        #    Exception.
        if (($node instanceof Text && $this instanceof Document) || ($node instanceof DocumentType && !$this instanceof Document)) {
            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
        }

        # 6. If parent is a document, and any of the statements below, switched on the
        #    interface node implements, are true, then throw a "HierarchyRequestError".
        if ($this instanceof Document) {
            # ↪ DocumentFragment
            #      If node has more than one element child or has a Text node child.
            #
            #      Otherwise, if node has one element child and either parent has an element
            #      child, child is a doctype, or child is non-null and a doctype is following
            #      child.
            if ($node instanceof DocumentFragment) {
                $nodeChildElementCount = $node->childElementCount;
                if ($nodeChildElementCount > 1 || $node->firstChild->walkFollowing(function($n) {
                    return ($n instanceof Text);
                }, true)->current() !== null) {
                    throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                } elseif ($nodeChildElementCount === 1) {
                    if ($this->childElementCount > 0 || $child instanceof DocumentType) {
                        throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                    }

                    if ($child !== null) {
                        $n = $child;
                        while ($n = $n->nextSibling) {
                            if ($n instanceof DocumentType) {
                                throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                            }
                        }
                    }
                }
            }

            # ↪ Element
            #      parent has an element child, child is a doctype, or child is non-null and a
            #      doctype is following child.
            elseif ($node instanceof Element) {
                if ($child instanceof DocumentType) {
                    throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                }

                if ($child !== null) {
                    $n = $child;
                    while ($n = $n->nextSibling) {
                        if ($n instanceof DocumentType) {
                            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                        }
                    }
                }

                if ($this->firstChild !== null && $this->firstChild->walkFollowing(function($n) {
                    return ($n instanceof Element);
                }, true)->current() !== null) {
                    throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                }
            }

            # ↪ DocumentType
            #      parent has a doctype child, child is non-null and an element is preceding
            #      child, or child is null and parent has an element child.
            elseif ($node instanceof DocumentType) {
                if ($this->firstChild !== null && $this->firstChild->walkFollowing(function($n) {
                    return ($n instanceof DocumentType);
                }, true)->current() !== null) {
                    throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                }

                if ($child !== null) {
                    $n = $child;
                    while ($n = $n->previousSibling) {
                        if ($n instanceof Element) {
                            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                        }
                    }
                } else {
                    if ($this->firstChild !== null && $this->firstChild->walkFollowing(function($n) {
                        return ($n instanceof Element);
                    }, true)->current() !== null) {
                        throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                    }
                }
            }
        }
    }
}
