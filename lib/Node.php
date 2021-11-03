<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\Framework\MagicProperties,
    MensBeam\HTML\Parser,
    MensBeam\HTML\Parser\NameCoercion;
use MensBeam\HTML\DOM\InnerNode\{
    Document as InnerDocument,
    Reflection
};


abstract class Node {
    use MagicProperties, NameCoercion;

    public const ELEMENT_NODE = 1;
    public const ATTRIBUTE_NODE = 2;
    public const TEXT_NODE = 3;
    public const CDATA_SECTION_NODE = 4;
    public const ENTITY_REFERENCE_NODE = 5; // legacy
    public const ENTITY_NODE = 6; // legacy
    public const PROCESSING_INSTRUCTION_NODE = 7;
    public const COMMENT_NODE = 8;
    public const DOCUMENT_NODE = 9;
    public const DOCUMENT_TYPE_NODE = 10;
    public const DOCUMENT_FRAGMENT_NODE = 11;
    public const NOTATION_NODE = 12; // legacy

    public const DOCUMENT_POSITION_DISCONNECTED = 0x01;
    public const DOCUMENT_POSITION_PRECEDING = 0x02;
    public const DOCUMENT_POSITION_FOLLOWING = 0x04;
    public const DOCUMENT_POSITION_CONTAINS = 0x08;
    public const DOCUMENT_POSITION_CONTAINED_BY = 0x10;
    public const DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC = 0x20;

    protected \DOMNode $innerNode;

    private static ?int $rand = null;

    protected function __get_baseURI(): string {
        # The baseURI getter steps are to return this’s node document’s document base
        # URL, serialized.
        #
        # The document base URL of a Document object is the absolute URL obtained by running these steps:
        $doc = ($this instanceof Document) ? $this : $this->ownerDocument;
        $base = $this->getInnerNode($doc)->getElementsByTagName('base');
        foreach ($base as $b) {
            $href = $b->getAttribute('href');
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
        // DEVIATION: Cannot have a browsing context in this implementation.
        # 3. Return document's URL.
        return $doc->URL;
    }

    protected function __get_childNodes(): NodeList {
        return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\NodeList', ($this instanceof Document) ? $this->innerNode : $this->innerNode->ownerDocument, $this->innerNode->childNodes);
    }

    protected function __get_firstChild(): ?Node {
        // PHP's DOM does this correctly already.
        if (!$value = $this->innerNode->firstChild) {
            return null;
        }

        return $this->getInnerDocument()->getWrapperNode($value);
    }

    protected function __get_isConnected(): bool {
        # The isConnected getter steps are to return true, if this is connected;
        # otherwise false.
        # An element is connected if its shadow-including root is a document.
        return ($this->getRootNode() instanceof Document);
    }

    protected function __get_lastChild(): ?Node {
        // PHP's DOM does this correctly already.
        if (!$value = $this->innerNode->lastChild) {
            return null;
        }

        return $this->getInnerDocument()->getWrapperNode($value);
    }

    protected function __get_previousSibling(): ?Node {
        // PHP's DOM does this correctly already.
        if (!$value = $this->innerNode->previousSibling) {
            return null;
        }

        return $this->getInnerDocument()->getWrapperNode($value);
    }

    protected function __get_nextSibling(): ?Node {
        // PHP's DOM does this correctly already.
        if (!$value = $this->innerNode->nextSibling) {
            return null;
        }

        return $this->getInnerDocument()->getWrapperNode($value);
    }

    protected function __get_nodeName(): string {
        # The nodeName getter steps are to return the first matching statement,
        # switching on the interface this implements:
        $nodeName = $this->innerNode->nodeName;

        # ↪ Element
        #     Its HTML-uppercased qualified name.
        if ($this instanceof Element) {
            // Uncoerce names if necessary
            return strtoupper(!str_contains(needle: 'U', haystack: $nodeName) ? $nodeName : $this->uncoerceName($nodeName));
        }
        // Attribute nodes and processing instructions need the node name uncoerced if
        // necessary
        elseif ($this instanceof Attr || $this instanceof ProcessingInstruction) {
            return (!str_contains(needle: 'U', haystack: $nodeName)) ? $nodeName : $this->uncoerceName($nodeName);
        }
        // While the DOM itself cannot create a doctype with an empty string as the
        // name, the HTML parser can. PHP's DOM cannot handle an empty string as the
        // name, so a single space (an invalid value) is used instead and coerced to an
        // empty string.
        elseif ($this instanceof DocumentType) {
            return $this->name;
        }

        // PHP's DOM handles everything correctly on everything else.
        return $nodeName;
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

    protected function __get_ownerDocument(): ?Document {
        # The ownerDocument getter steps are to return null, if this is a document;
        # otherwise this’s node document.
        // PHP's DOM does this correctly already.
        if ($this instanceof Document || !$ownerDocument = $this->innerNode->ownerDocument) {
            return null;
        }

        return $this->innerNode->ownerDocument->getWrapperNode($ownerDocument);
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

        return $this->getInnerDocument()->getWrapperNode($parent);
    }

    protected function __get_textContent(): ?string {
        if ($this instanceof Document || $this instanceof DocumentType) {
            return null;
        }

        // PHP's DOM does this correctly already with the exception of Document and
        // DocumentType.
        return $this->innerNode->textContent;
    }

    protected function __set_textContent(string $value): void {
        # The textContent setter steps are to, if the given value is null, act as if it
        # was the empty string instead, and then do as described below, switching on the
        # interface this implements:

        # ↪ DocumentFragment
        # ↪ Element
        #      String replace all with the given value within this.
        if ($this instanceof DocumentFragment || $this instanceof Element) {
            # To string replace all with a string string within a node parent, run these
            # steps:
            // PHP's DOM segfaults when attempting to set textContent on a DocumentFragment,
            // and when setting textContent on an Element it incorrectly destroys all
            // nodes replaced within the element from memory immediately regardless of
            // whether they're stored already in code or not.

            # 1. Let node be null.
            $node = null;

            # 2. If string is not the empty string, then set node to a new Text node whose
            #    data is string and node document is parent’s node document.
            // string is $value
            if ($value !== '') {
                $node = $this->innerNode->ownerDocument->createTextNode($value);
            }

            # 3. Replace all with node within parent.
            while ($this->innerNode->hasChildNodes()) {
                $this->innerNode->removeChild($this->innerNode->firstChild);
            }

            if ($value !== '') {
                $this->innerNode->appendChild($node);
            }
        }
        # ↪ Attr
        #      This's value
        elseif ($this instanceof Attr) {
            $this->innerNode->value = $value;
        }
        # ↪ CharacterData
        #      Replace data with node this, offset 0, count this’s length, and data the given
        #      value.
        elseif ($this instanceof CharacterData) {
            $this->innerNode->data = $value;
        }
        # ↪ Otherwise
        #      Do nothing.
        // PHP's DOM allows the setting of textContent in Document and DocumentType so
        // this catches that violation.
    }


    protected function __construct(\DOMNode $innerNode) {
        $this->innerNode = $innerNode;
    }


    public function appendChild(Node $node): Node {
        // Aside from pre-insertion validity PHP's DOM does this correctly already.
        $this->preInsertionValidity($node);
        $this->innerNode->appendChild($this->getInnerNode($node));
        return $node;
    }

    public function cloneNode(bool $deep = false): Node {
        # The cloneNode(deep) method steps are:
        // PHP's DOM mostly does this correctly with the exception of not cloning
        // doctypes. However, the entire process needs to be done manually because of
        // templates.

        # 1. If this is a shadow root, then throw a "NotSupportedError" DOMException.
        // DEVIATION: There is no scripting in this implementation

        # 2. Return a clone of this, with the clone children flag set if deep is true.
        return $this->cloneWrapperNode($this, (!$this instanceof Document) ? $this->ownerDocument : $this, $deep);
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
        $innerNode1 = $this->getInnerNode($other);
        $innerNode2 = $this->innerNode;

        # 3. Let attr1 and attr2 be null.
        $attr1 = $attr2 = null;

        # 4. If node1 is an attribute, then set attr1 to node1 and node1 to attr1’s
        #   element.
        if ($node1 instanceof Attr) {
            $attr1 = $innerNode1;
            $node1 = $attr1->ownerElement;
            $innerNode1 = $innerNode1->ownerElement;
        }

        # 5. If node2 is an attribute, then:
        if ($node2 instanceof Attr) {
            # 1. Set attr2 to node2 and node2 to attr2’s element.
            $attr2 = $innerNode2;
            $node2 = $attr2->ownerElement;
            $innerNode2 = $innerNode2->ownerElement;

            # 2. If attr1 and node1 are non-null, and node2 is node1, then:
            if ($attr1 !== null && $node1 !== null && $node2 === $node1) {
                # 1. For each attr in node2’s attribute list:
                $attributes = $innerNode2->attributes;
                // Have to check for null because PHP DOM violates the spec and returns null when empty
                if ($attributes !== null) {
                    foreach ($attributes as $attr) {
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

        $n = $innerNode1;
        do {
            $root1 = $n;
        } while ($n = $n->parentNode);

        $n = $innerNode2;
        do {
            $root2 = $n;
        } while ($n = $n->parentNode);

        if ($node1 === null || $node2 === null || $root1 !== $root2) {
            return Node::DOCUMENT_POSITION_DISCONNECTED + Node::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC + ((self::$rand === 0) ? Node::DOCUMENT_POSITION_PRECEDING : Node::DOCUMENT_POSITION_FOLLOWING);
        }

        # 7. If node1 is an ancestor of node2 and attr1 is null, or node1 is node2 and attr2
        #    is non-null, then return the result of adding DOCUMENT_POSITION_CONTAINS to
        #    DOCUMENT_POSITION_PRECEDING.
        if (($node1 === $node2 && $attr2 !== null) || ($attr1 === null && $this->containsInner($innerNode1, $innerNode2))) {
            return Node::DOCUMENT_POSITION_CONTAINS + Node::DOCUMENT_POSITION_PRECEDING;
        }

        # 8. If node1 is a descendant of node2 and attr2 is null, or node1 is node2 and attr1
        #    is non-null, then return the result of adding DOCUMENT_POSITION_CONTAINED_BY to
        #    DOCUMENT_POSITION_FOLLOWING.
        if (($node1 === $node2 && $attr1 !== null) || ($attr2 === null && $this->containsInner($innerNode2, $innerNode1))) {
            return Node::DOCUMENT_POSITION_CONTAINED_BY + Node::DOCUMENT_POSITION_FOLLOWING;
        }

        # 9. If node1 is preceding node2, then return DOCUMENT_POSITION_PRECEDING.
        $n = $innerNode2;
        while ($n = $n->previousSibling) {
            if ($n === $innerNode1) {
                return Node::DOCUMENT_POSITION_PRECEDING;
            }
        }

        # 10. Return DOCUMENT_POSITION_FOLLOWING.
        return Node::DOCUMENT_POSITION_FOLLOWING;
    }

    public function contains(?Node $other): bool {
        return $this->containsInner($this->innerNode, $this->getInnerNode($other));
    }

    public function getRootNode(): ?Node {
        # The getRootNode(options) method steps are to return this’s shadow-including
        # root if options["composed"] is true; otherwise this’s root.
        // DEVIATION: This implementation does not have scripting, so there's no Shadow
        // DOM. Therefore, there isn't a need for the options parameter.

        # The root of an object is itself, if its parent is null, or else it is the root
        # of its parent. The root of a tree is any object participating in that tree
        # whose parent is null.
        $node = $this->innerNode;
        if ($node->parentNode === null) {
            return $this;
        }

        $n = $node;
        while ($n = $n->parentNode) {
            $root = $n;
        }
        return (!$root instanceof InnerDocument) ? $root->ownerDocument->getWrapperNode($root) : $root->wrapperNode;
    }

    public function hasChildNodes(): bool {
        // PHP's DOM does this correctly already.
        return $this->innerNode->hasChildNodes();
    }

    public function insertBefore(Node $node, ?Node $child = null): Node {
        # The insertBefore(node, child) method steps are to return the result of
        # pre-inserting node into this before child.
        // Aside from pre-insertion validity PHP's DOM does this correctly already.
        $this->preInsertionValidity($node, $child);
        $this->innerNode->insertBefore($this->getInnerNode($node), $this->getInnerNode($child));
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
        return ($this->locateNamespace($this->innerNode, null) === $namespace);
    }

    public function isEqualNode(?Node $otherNode) {
        # The isEqualNode(otherNode) method steps are to return true if otherNode is
        # non-null and this equals otherNode; otherwise false.
        return $this->isEqualInnerNode($this->innerNode, $this->getInnerNode($otherNode));
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
            return $this->locateNamespacePrefix($this->innerNode, $namespace);
        }

        # ↪ Document
        elseif ($this instanceof Document) {
            $documentElement = $this->documentElement;
            # Return the result of locating a namespace prefix for its document element, if
            # its document element is non-null; otherwise null.
            return ($documentElement !== null) ? $this->locateNamespacePrefix($this->getInnerNode($documentElement), $namespace) : null;
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
            return $this->locateNamespacePrefix($this->getInnerNode($this->ownerElement), $namespace);
        }

        # ↪ Otherwise
        #      Return the result of locating a namespace prefix for its parent element,
        #      if its parent element is non-null; otherwise null.
        $parentElement = $this->parentElement;
        return ($parentElement !== null) ? $this->locateNamespacePrefix($this->getInnerNode($parentElement), $namespace) : null;
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
        return $this->locateNamespace($this->innerNode, $prefix);
    }

    public function normalize(): void {
        // PHP's DOM does this correctly already.
        $this->innerNode->normalize();
    }

    public function removeChild(Node $child): Node {
        // PHP's DOM does this correctly already.
        return $this->getInnerDocument()->getWrapperNode($this->innerNode->removeChild($this->getInnerNode($child)));
    }

    public function replaceChild(Node $node, Node $child): Node {
        $wrapperNode = $node;
        $node = $this->getInnerNode($node);
        $child = $this->getInnerNode($child);
        $inner = $this->innerNode;

        # The replaceChild(node, child) method steps are to return the result of
        # replacing child with node within this.
        // PHP's DOM has some issues due to not checking for some edge cases the DOM
        // spec outlines for Node::replaceChild, so let's follow those before using the
        // PHP DOM to replace.

        # To replace a child with node within a parent, run these steps:
        #
        # 1. If parent is not a Document, DocumentFragment, or Element node, then throw
        #    a "HierarchyRequestError" DOMException.
        if (!$inner instanceof InnerDocument && !$inner instanceof \DOMDocumentFragment && !$inner instanceof \DOMElement) {
            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
        }

        # 2. If node is a host-including inclusive ancestor of parent, then throw a
        #    "HierarchyRequestError" DOMException.
        // The specification makes no mention of checking to see if node is a
        // host-including inclusive ancestor of child, but it should. All browsers check
        // for this.
        if ($this->containsInner($node, $inner) || $this->containsInner($node, $child)) {
            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
        }

        # 3. If child’s parent is not parent, then throw a "NotFoundError" DOMException.
        if ($child->parentNode !== $inner) {
            throw new DOMException(DOMException::NOT_FOUND);
        }

        # 4. If node is not a DocumentFragment, DocumentType, Element, or CharacterData
        #    node, then throw a "HierarchyRequestError" DOMException.
        if (!$node instanceof \DOMDocumentFragment && !$node instanceof \DOMDocumentType && !$node instanceof \DOMElement && !$node instanceof \DOMCharacterData) {
            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
        }

        # 5. If either node is a Text node and parent is a document, or node is a
        #    doctype and parent is not a document, then throw a "HierarchyRequestError"
        #    DOMException.
        if (($node instanceof \DOMText && $inner instanceof InnerDocument) || ($node instanceof \DOMDocumentType && !$inner instanceof InnerDocument)) {
            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
        }

        # 6. If parent is a document, and any of the statements below, switched on the
        #    interface node implements, are true, then throw a "HierarchyRequestError".
        if ($inner instanceof InnerDocument) {
            # ↪ DocumentFragment
            #      If node has more than one element child or has a Text node child.
            #
            #      Otherwise, if node has one element child and either parent has an element
            #      child that is not child or a doctype is following child.
            if ($node instanceof \DOMDocumentFragment) {
                $nodeChildElementCount = $node->childElementCount;
                if ($nodeChildElementCount > 1) {
                    throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                }

                $n = $node->firstChild;
                if ($n !== null) {
                    do {
                        if ($n instanceof \DOMText) {
                            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                        }
                    } while ($n = $n->nextSibling);
                }

                if ($nodeChildElementCount === 1) {
                    $n = $inner->firstChild;
                    if ($n !== null) {
                        $beforeChild = ($n !== $child);
                        do {
                            if (($n instanceof \DOMElement && $n !== $child) || (!$beforeChild && $n instanceof \DOMDocumentType)) {
                                throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                            } elseif ($n === $child) {
                                $beforeChild = false;
                            }
                        } while ($n = $n->nextSibling);
                    }
                }
            }

            # ↪ Element
            #      parent has an element child that is not child or a doctype is following
            #      child.
            elseif ($node instanceof \DOMElement) {
                $n = $inner->firstChild;
                if ($n !== null) {
                    $beforeChild = ($n !== $child);
                    do {
                        if (($n instanceof \DOMElement && $n !== $child) || (!$beforeChild && $n instanceof \DOMDocumentType)) {
                            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                        } elseif ($n === $child) {
                            $beforeChild = false;
                        }
                    } while ($n = $n->nextSibling);
                }
            }

            # ↪ DocumentType
            #      parent has a doctype child that is not child, or an element is preceding
            #      child.
            elseif ($node instanceof \DOMDocumentType) {
                $n = $inner->firstChild;
                if ($n !== null) {
                    $beforeChild = ($n !== $child);
                    do {
                        if (($n instanceof \DOMDocumentType && $n !== $child) || $beforeChild && $n instanceof \DOMElement) {
                            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                        } elseif ($n === $child) {
                            $beforeChild = false;
                        }
                    } while ($n = $n->nextSibling);
                }
            }
        }

        // PHP's DOM does fine with the rest of the steps.
        $inner->replaceChild($node, $child);
        return $wrapperNode;
    }


    protected function cloneInnerNode(\DOMNode $node, ?InnerDocument $document, bool $cloneChildren = false): \DOMNode {
        // This method exists so when cloning or importing documents, fragments, and
        // elements every node doesn't need to be immediately wrapped. It is also
        // helpful when importing after parsing.

        # To clone a node, with an optional document and clone children flag, run these steps:
        #
        # 1. If document is not given, let document be node’s node document.
        // Document will always be provided
        $import = ($document !== $node->ownerDocument);

        # 2. If node is an element, then:
        #        1. Let copy be the result of creating an element, given document, node’s local
        #           name, node’s namespace, node’s namespace prefix, and node’s is value, with the
        #           synchronous custom elements flag unset.
        #        2. For each attribute in node’s attribute list:
        #              1. Let copyAttribute be a clone of attribute.
        #              2. Append copyAttribute to copy.
        if ($node instanceof \DOMElement) {
            $copy = ($import) ? $document->importNode($node) : $node->cloneNode();

            // PHP DOM doesn't import id attributes where
            // NonElementParentNode::getElementById can see them, so let's fix that.
            if ($id = $copy->getAttributeNode('id')) {
                $copy->setIdAttributeNode($id, true);
            }
        }

        # 3. Otherwise, let copy be a node that implements the same interfaces as node, and
        #    fulfills these additional requirements, switching on the interface node
        #    implements:
        #
        # ↪ Document
        #      Set copy’s encoding, content type, URL, origin, type, and mode to those of node.
        elseif ($node instanceof \DOMDocumentType) {
            // OPTIMIZATION: No need for the other steps as the DocumentType node is created
            // using this document's implementation
            return $document->implementation->createDocumentType($node->name, $node->publicId, $node->systemId);
        }

        # ↪ Attr
        #      Set copy’s namespace, namespace prefix, local name, and value to those of node.
        # ↪ Text
        # ↪ Comment
        #      Set copy’s data to that of node.
        # ↪ ProcessingInstruction
        #      Set copy’s target and data to those of node.
        elseif ($node instanceof \DOMAttr || $node instanceof \DOMText || $node instanceof \DOMComment || $node instanceof \DOMProcessingInstruction) {
            // OPTIMIZATION: No need for the other steps as PHP's DOM handles this fine
            return ($import) ? $document->importNode($node) : $node->cloneNode();
        }

        # ↪ Otherwise
        #      Do nothing.
        // This is for documents. There won't be an instance of cloning an inner document.

        # 4. Set copy’s node document and document to copy, if copy is a document, and
        # set copy’s node document to document otherwise.
        // PHP's DOM does this already

        if ($cloneChildren) {
            # 5. Run any cloning steps defined for node in other applicable specifications
            #    and pass copy, node, document and the clone children flag if set, as
            #    parameters.
            if ($node instanceof \DOMElement && $node->namespaceURI === null && $node->nodeName === 'template') {
                # The cloning steps for a template element node being cloned to a copy copy must
                # run the following steps:
                #
                # 1. If the clone children flag is not set in the calling clone algorithm, return.
                // This is done with the if statements above.

                # 2. Let copied contents be the result of cloning all the children of node's template
                #    contents, with document set to copy's template contents's node document, and
                #    with the clone children flag set.
                # 3. Append copied contents to copy's template contents.
                // Template contents are stored in the wrapper nodes.
                $copyWrapperContent = $copy->ownerDocument->getWrapperNode($copy)->content;

                // Need to check to see if what is being cloned is a MensBeam inner node or not.
                // Most of the time this will be the case, but if a document is being parsed
                // that has template elements it won't be; instead the template element's
                // children need to be appended to the inner content DOMDocumentFragment.
                if ($node->ownerDocument instanceof InnerDocument) {
                    $nodeWrapperContent = $node->ownerDocument->getWrapperNode($node)->content;
                    if ($nodeWrapperContent->hasChildNodes()) {
                        $copyWrapperContent->appendChild($this->cloneWrapperNode($nodeWrapperContent, $document->wrapperNode, true));
                    }
                } else {
                    $copyContent = $this->getInnerNode($copyWrapperContent);
                    $childNodes = $node->childNodes;
                    foreach ($childNodes as $child) {
                        $copyContent->appendChild($this->cloneInnerNode($child, $document, true));
                    }

                    // Step #6 isn't necessary now; just return the copy.
                    return $copy;
                }
            }

            # 6. If the clone children flag is set, clone all the children of node and append
            #    them to copy, with document as specified and the clone children flag being
            #    set.
            if ($node instanceof \DOMElement || $node instanceof \DOMDocumentFragment) {
                $childNodes = $node->childNodes;
                foreach ($childNodes as $child) {
                    $copy->appendChild($this->cloneInnerNode($child, $document, true));
                }
            }
        }

        # 7. Return copy.
        return $copy;
    }

    protected function cloneWrapperNode(Node $node, Document $document, bool $cloneChildren = false): Node {
        // Wrapped nodes are cloned in a way where all descendants of $node except for
        // templates are cloned without creating a wrapper node, making the process a
        // lot faster. This necessitates the extra protected methods.

        # To clone a node, with an optional document and clone children flag, run these steps:
        #
        # 1. If document is not given, let document be node’s node document.
        // Will do this later once node's type is determined.

        # 2. If node is an element, then:
        #        1. Let copy be the result of creating an element, given document, node’s local
        #           name, node’s namespace, node’s namespace prefix, and node’s is value, with the
        #           synchronous custom elements flag unset.
        #        2. For each attribute in node’s attribute list:
        #              1. Let copyAttribute be a clone of attribute.
        #              2. Append copyAttribute to copy.
        // PHP's DOM can do this part correctly by shallow cloning, so it will be
        // handled instead in the "Otherwise" section of step #3.

        # 3. Otherwise, let copy be a node that implements the same interfaces as node, and
        #    fulfills these additional requirements, switching on the interface node
        #    implements:
        #
        # ↪ Document
        #      Set copy’s encoding, content type, URL, origin, type, and mode to those of node.
        if ($node instanceof Document) {
            $document = new Document();
            $innerDocument = $this->getInnerNode($document);
            $import = true;

            if ($node->characterSet !== 'UTF-8' || $node->compatMode !== 'CSS1Compat' || $node->contentType !== 'text/html' || $node->URL !== 'about:blank') {
                Reflection::setProtectedProperties($document, [
                    '_characterSet' => $node->characterSet,
                    '_compatMode' => $node->compatMode,
                    '_contentType' => $node->contentType,
                    '_URL' => $node->URL
                ]);
            }

            $copy = $innerDocument;
            $copyWrapper = $document;
            $innerNode = $node->innerNode;
        } else {
            $import = ($document !== $node->ownerDocument);
            $innerNode = $this->getInnerNode($node);
            $innerDocument = $this->getInnerNode($document);

            if ($node instanceof Element) {
                $copy = ($import) ? $innerDocument->importNode($innerNode) : $innerNode->cloneNode();
                $copyWrapper = $innerDocument->getWrapperNode($copy);

                // PHP DOM doesn't import id attributes where
                // NonElementParentNode::getElementById can see them, so let's fix that.
                if ($id = $copy->getAttributeNode('id')) {
                    $copy->setIdAttributeNode($id, true);
                }
            }

            # ↪ DocumentType
            #      Set copy’s name, public ID, and system ID to those of node.
            if ($node instanceof DocumentType) {
                // OPTIMIZATION: No need for the other steps as the DocumentType node is created
                // using this document's implementation
                return $document->implementation->createDocumentType($node->name, $node->publicId, $node->systemId);
            }

            # ↪ Attr
            #      Set copy’s namespace, namespace prefix, local name, and value to those of node.
            # ↪ Text
            # ↪ Comment
            #      Set copy’s data to that of node.
            # ↪ ProcessingInstruction
            #      Set copy’s target and data to those of node.
            elseif ($node instanceof Attr || $node instanceof Text || $node instanceof Comment || $node instanceof ProcessingInstruction) {
                // OPTIMIZATION: No need for the other steps as PHP's DOM handles this fine
                return $innerDocument->getWrapperNode(($import) ? $innerDocument->importNode($innerNode) : $innerNode->cloneNode());
            }

            # ↪ Otherwise
            #      Do nothing.
            else {
                $copy = ($import) ? $innerDocument->importNode($innerNode) : $innerNode->cloneNode();
                $copyWrapper = $innerDocument->getWrapperNode($copy);
            }
        }

        # 4. Set copy’s node document and document to copy, if copy is a document, and
        # set copy’s node document to document otherwise.
        // PHP's DOM does this already

        if ($cloneChildren) {
            # 5. Run any cloning steps defined for node in other applicable specifications
            #    and pass copy, node, document and the clone children flag if set, as
            #    parameters.
            if ($node instanceof HTMLTemplateElement) {
                # The cloning steps for a template element node being cloned to a copy copy must
                # run the following steps:
                #
                # 1. If the clone children flag is not set in the calling clone algorithm, return.
                // This is done with the if statements above.

                # 2. Let copied contents be the result of cloning all the children of node's template
                #    contents, with document set to copy's template contents's node document, and
                #    with the clone children flag set.
                # 3. Append copied contents to copy's template contents.
                $copyWrapper->content->appendChild($this->cloneWrapperNode($node->content, $document, true));
            }

            # 6. If the clone children flag is set, clone all the children of node and append
            #    them to copy, with document as specified and the clone children flag being
            #    set.
            if ($node instanceof Element || $node instanceof DocumentFragment || $node instanceof Document) {
                $childNodes = $innerNode->childNodes;
                foreach ($childNodes as $child) {
                    $copy->appendChild($this->cloneInnerNode($child, $innerDocument, true));
                }
            }
        }

        # 7. Return copy.
        return $copyWrapper;
    }

    protected function containsInner(\DOMNode $node, \DOMNode $other): bool {
        # The contains(other) method steps are to return true if other is an inclusive
        # descendant of this; otherwise false (including when other is null).
        $n = $other;
        while ($n = $n->parentNode) {
            if ($n === $node) {
                return true;
            }
        }

        return false;
    }

    protected function getInnerDocument(): InnerDocument {
        return ($this instanceof Document) ? $this->innerNode : $this->innerNode->ownerDocument;
    }

    protected function getInnerNode(?Node $node = null): \DOMNode {
        if ($node === null || $node === $this) {
            return $this->innerNode;
        }

        // Using reflection to get the inner node (for everything but documents) rather
        // than polling the inner owner document's node map is a good bit faster. It's
        // wrong to do this, but hey... it's faster.
        return Reflection::getProtectedProperty($node, 'innerNode');
    }

    protected function isEqualInnerNode(\DOMNode $thisNode, \DOMNode $otherNode) {
        # A node A equals a node B if all of the following conditions are true:
        #
        # • A and B implement the same interfaces.
        if ($thisNode::class !== $otherNode::class) {
            return false;
        }

        # • The following are equal, switching on the interface A implements:
        #
        # ↪ DocumentType
        #      Its name, public ID, and system ID.
        if ($thisNode instanceof \DOMDocumentType) {
            if ($thisNode->name !== $otherNode->name || $thisNode->publicId !== $otherNode->publicId || $thisNode->systemId !== $thisNode->publicId) {
                return false;
            }
        }
        # ↪ Element
        #      Its namespace, namespace prefix, local name, and its attribute list’s size.
        elseif ($thisNode instanceof \DOMElement) {
            $otherAttributes = $otherNode->attributes;
            if ($thisNode->namespaceURI !== $otherNode->namespaceURI || $thisNode->prefix !== $otherNode->prefix || $thisNode->localName !== $otherNode->localName || $thisNode->attributes->length !== $otherAttributes->length) {
                return false;
            }

            # • If A is an element, each attribute in its attribute list has an attribute that
            #   equals an attribute in B’s attribute list.
            $thisNodeAttributes = $thisNode->attributes;
            foreach ($thisNodeAttributes as $key => $attr) {
                if (!$this->isEqualInnerNode($attr, $otherAttributes[$key])) {
                    return false;
                }
            }
        }
        # ↪ Attr
        #       Its namespace, local name, and value.
        elseif ($thisNode instanceof \DOMAttr) {
            if ($thisNode->namespaceURI !== $otherNode->namespaceURI || $thisNode->localName !== $otherNode->localName || $thisNode->value !== $otherNode->value) {
                return false;
            }
        }
        # ↪ Text
        # ↪ Comment
        #      Its data.
        elseif ($thisNode instanceof \DOMText || $thisNode instanceof \DOMComment) {
            if ($thisNode->data !== $otherNode->data) {
                return false;
            }
        }

        if ($thisNode instanceof \DOMDocument || $thisNode instanceof \DOMDocumentFragment || $thisNode instanceof \DOMElement) {
            # • A and B have the same number of children.
            if ($thisNode->childNodes->length !== $otherNode->childNodes->length) {
                return false;
            }

            # • Each child of A equals the child of B at the identical index.
            foreach ($thisNode->childNodes as $key => $child) {
                $other = $otherNode->childNodes[$key];
                if (!$this->isEqualInnerNode($child, $other)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function locateNamespace(\DOMNode $node, ?string $prefix = null): ?string {
        # To locate a namespace for a node using prefix, switch on the interface node
        # implements:
        #
        # ↪ Element
        if ($node instanceof \DOMElement) {
            // Work around PHP DOM HTML namespace bug
            if ($node->namespaceURI === null && !$node->ownerDocument->getWrapperNode($node->ownerDocument) instanceof XMLDocument) {
                $namespace = Parser::HTML_NAMESPACE;
            } else {
                $namespace = $node->namespaceURI;
            }

            // Work around another PHP DOM bug where \DOMNode::prefix returns an empty string if empty instead of null
            $nodePrefix = $node->prefix;
            if ($nodePrefix === '') {
                $nodePrefix = null;
            }

            # 1. If its namespace is non-null and its namespace prefix is prefix, then return
            #    namespace.
            if ($namespace !== null && $nodePrefix === $prefix) {
                return $namespace;
            }

            # 2. If it has an attribute whose namespace is the XMLNS namespace, namespace prefix
            #    is "xmlns", and local name is prefix, or if prefix is null and it has an
            #    attribute whose namespace is the XMLNS namespace, namespace prefix is null, and
            #    local name is "xmlns", then return its value if it is not the empty string, and
            #    null otherwise.
            $attributes = $node->attributes;
            // Have to check for null because PHP DOM violates the spec and returns null when empty
            if ($attributes !== null) {
                foreach ($attributes as $attr) {
                    // This is not in the specification at all, but all the browsers do this.
                    // 'xmlns:xmlns' is the same as 'xmlns' because the XML developers want to make
                    // our lives miserable, so if we followed the specification as written we could
                    // get an incorrect answer. So, also check to see if the local name is not
                    // 'xmlns', too.
                    if (($attr->namespaceURI === Parser::XMLNS_NAMESPACE && $attr->prefix === 'xmlns' && $attr->localName === $prefix) || ($prefix === null && $attr->namespaceURI === Parser::XMLNS_NAMESPACE && ($attr->prefix === null || $attr->prefix === '' || $attr->prefix === 'xmlns') && $attr->localName === 'xmlns')) {
                        return ($attr->value !== '') ? $attr->value : null;
                    }
                }
            }

            # 3. If its parent element is null, then return null.
            $parentElement = $node->parentNode;
            if (!$parentElement instanceof \DOMElement) {
                return null;
            }

            # 4. Return the result of running locate a namespace on its parent element using
            #    prefix.
            return $this->locateNamespace($parentElement, $prefix);
        }

        # ↪ Document
        elseif ($node instanceof InnerDocument) {
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
        elseif ($node instanceof \DOMDocumentType || $node instanceof \DOMDocumentFragment) {
            # Return null.
            return null;
        }

        # ↪ Attr
        elseif ($node instanceof \DOMAttr) {
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
        else {
            $parentElement = $node->parentNode;
            if (!$parentElement instanceof \DOMElement) {
                return null;
            }
        }

        # 2. Return the result of running locate a namespace on its parent element using
        #    prefix.
        return $this->locateNamespace($parentElement, $prefix);
    }

    protected function locateNamespacePrefix(\DOMElement $element, ?string $namespace = null) {
        # To locate a namespace prefix for an element using namespace, run these steps:
        #
        # 1. If element’s namespace is namespace and its namespace prefix is non-null,
        #    then return its namespace prefix.
        // PHP DOM uses an empty string instead of null...
        $elementPrefix = $element->prefix;
        if ($elementPrefix !== null && $elementPrefix !== '') {
            if ($element->namespaceURI === $namespace) {
                // Need to uncoerce if necessary...
                return (!str_contains(needle: 'U', haystack: $elementPrefix)) ? $elementPrefix : $this->uncoerceName($elementPrefix);
            }
        }

        # 2. If element has an attribute whose namespace prefix is "xmlns" and value is
        #    namespace, then return element’s first such attribute’s local name.
        $attributes = $element->attributes;
        // Have to check for null because PHP DOM violates the spec and returns null when empty
        if ($attributes !== null) {
            foreach ($attributes as $attr) {
                // This is not in the specification at all, but all the browsers do this.
                // 'xmlns:xmlns' is the same as 'xmlns' because the XML developers want to make
                // our lives miserable, so if we followed the specification as written we could
                // get an incorrect answer. So, also check to see if the local name is not
                // 'xmlns', too.
                $localName = $attr->localName;
                if ($attr->prefix === 'xmlns' && $localName !== 'xmlns' && $attr->value === $namespace) {
                    // Need to uncoerce if necessary...
                    return (!str_contains(needle: 'U', haystack: $localName)) ? $localName : $this->uncoerceName($localName);
                }
            }
        }

        # 3. If element’s parent element is not null, then return the result of running
        #    locate a namespace prefix on that element using namespace.
        $parentElement = $element->parentNode;
        if ($parentElement instanceof \DOMElement) {
            return $this->locateNamespacePrefix($parentElement, $namespace);
        }

        # Return null.
        return null;
    }

    protected function preInsertionValidity(Node $node, ?Node $child = null) {
        $parent = $this->innerNode;
        $node = $this->getInnerNode($node);
        if ($child !== null) {
            $child = $this->getInnerNode($child);
        }

        # 1. If parent is not a Document, DocumentFragment, or Element node, then throw
        #    a "HierarchyRequestError" Exception.
        if (!$parent instanceof InnerDocument && !$parent instanceof \DOMDocumentFragment && !$parent instanceof \DOMElement) {
            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
        }

        # 2. If node is a host-including inclusive ancestor of parent, then throw a
        #    "HierarchyRequestError" Exception.
        #
        # An object A is a host-including inclusive ancestor of an object B, if either
        # A is an inclusive ancestor of B, or if B’s root has a non-null host and A is a
        # host-including inclusive ancestor of B’s root’s host.
        if ($node->parentNode !== null) {
            if ($parent->parentNode !== null && ($parent === $node || $this->containsInner($node, $parent))) {
                throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
            } else {
                $n = $parent;
                do {
                    $parentRoot = $n;
                } while ($n = $n->parentNode);

                if ($parentRoot instanceof \DOMDocumentFragment) {
                    $wrappedParentRoot = $parentRoot->ownerDocument->getWrapperNode($parentRoot);
                    $parentRootHost = $this->getInnerNode(Reflection::getProtectedProperty($wrappedParentRoot, 'host')->get());
                    if ($parentRootHost !== null && ($parentRootHost === $node || $this->containsInner($node, $parentRootHost))) {
                        throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                    }
                }
            }
        }

        # 3. If child is non-null and its parent is not parent, then throw a
        #    "NotFoundError" Exception.
        if ($child !== null && ($child->parentNode === null || $child->parentNode !== $parent)) {
            throw new DOMException(DOMException::NOT_FOUND);
        }

        # 4. If node is not a DocumentFragment, DocumentType, Element, Text,
        #    ProcessingInstruction, or Comment node, then throw a "HierarchyRequestError"
        #    Exception.
        if (!$node instanceof \DOMDocumentFragment && !$node instanceof \DOMDocumentType && !$node instanceof \DOMElement && !$node instanceof \DOMText && !$node instanceof \DOMProcessingInstruction && !$node instanceof \DOMComment) {
            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
        }

        # 5. If either node is a Text node and parent is a document, or node is a
        #    doctype and parent is not a document, then throw a "HierarchyRequestError"
        #    Exception.
        if (($node instanceof \DOMText && $parent instanceof \DOMDocument) || ($node instanceof \DOMDocumentType && !$parent instanceof InnerDocument)) {
            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
        }

        # 6. If parent is a document, and any of the statements below, switched on the
        #    interface node implements, are true, then throw a "HierarchyRequestError".
        if ($parent instanceof InnerDocument) {
            # ↪ DocumentFragment
            #      If node has more than one element child or has a Text node child.
            #
            #      Otherwise, if node has one element child and either parent has an element
            #      child, child is a doctype, or child is non-null and a doctype is following
            #      child.
            if ($node instanceof \DOMDocumentFragment) {
                $nodeChildElementCount = $node->childElementCount;
                if ($nodeChildElementCount > 1) {
                    throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                } else {
                    $n = $node->firstChild;
                    if ($n !== null) {
                        do {
                            if ($n instanceof \DOMText) {
                                throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                            }
                        } while ($n = $n->nextSibling);
                    }
                }

                if ($nodeChildElementCount === 1) {
                    if ($parent->childElementCount > 0 || $child instanceof \DOMDocumentType) {
                        throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                    }

                    if ($child !== null) {
                        $n = $child;
                        while ($n = $n->nextSibling) {
                            if ($n instanceof \DOMDocumentType) {
                                throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                            }
                        }
                    }
                }
            }

            # ↪ Element
            #      parent has an element child, child is a doctype, or child is non-null and a
            #      doctype is following child.
            elseif ($node instanceof \DOMElement) {
                if ($child instanceof \DOMDocumentType) {
                    throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                }

                if ($child !== null) {
                    $n = $child;
                    while ($n = $n->nextSibling) {
                        if ($n instanceof \DOMDocumentType) {
                            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                        }
                    }
                }

                if ($parent->firstChild !== null) {
                    $n = $parent->firstChild;
                    do {
                        if ($n instanceof \DOMElement) {
                            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                        }
                    } while ($n = $n->nextSibling);
                }
            }

            # ↪ DocumentType
            #      parent has a doctype child, child is non-null and an element is preceding
            #      child, or child is null and parent has an element child.
            elseif ($node instanceof \DOMDocumentType) {
                $firstChild = $parent->firstChild;
                if ($firstChild !== null) {
                    $n = $firstChild;
                    do {
                        if ($n instanceof \DOMDocumentType || ($child === null && $n instanceof \DOMElement)) {
                            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                        }
                    } while ($n = $n->nextSibling);
                }

                if ($child !== null) {
                    $n = $child;
                    while ($n = $n->previousSibling) {
                        if ($n instanceof \DOMElement) {
                            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
                        }
                    }
                }
            }
        }
    }

    public function __toString() {
        return $this->ownerDocument->serialize($this);
    }
}
