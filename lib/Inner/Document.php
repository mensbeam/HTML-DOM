<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\Inner;
use MensBeam\Framework\MagicProperties;
use MensBeam\HTML\DOM\{
    Document as WrapperDocument,
    DOMException,
    Node as WrapperNode,
    XMLDocument as WrapperXMLDocument
};


class Document extends \DOMDocument {
    use MagicProperties;

    // Used for validation. Not sure where to put them where they wouldn't be
    // exposed unnecessarily to the public API.
    public const NAME_PRODUCTION_REGEX = '/^[:A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}][:A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}-\.0-9\x{B7}\x{0300}-\x{036F}\x{203F}-\x{2040}]*$/Su';
    public const QNAME_PRODUCTION_REGEX = '/^([A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}][A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}-\.0-9\x{B7}\x{0300}-\x{036F}\x{203F}-\x{2040}]*:)?[A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}][A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}-\.0-9\x{B7}\x{0300}-\x{036F}\x{203F}-\x{2040}]*$/Su';

    protected NodeCache $nodeCache;
    protected \WeakReference $_wrapperNode;

    protected function __get_wrapperNode(): WrapperNode {
        return $this->_wrapperNode->get();
    }

    private static ?string $parentNamespace = null;


    public function __construct(WrapperDocument $wrapperNode) {
        parent::__construct();
        parent::registerNodeClass('DOMDocument', self::class);

        $this->nodeCache = new NodeCache();
        // Use a weak reference here to prevent a circular reference
        $this->_wrapperNode = \WeakReference::create($wrapperNode);

        if (self::$parentNamespace === null) {
            self::$parentNamespace = substr(__NAMESPACE__, 0, strrpos(__NAMESPACE__, '\\'));
        }
    }


    public function getWrapperNode(\DOMNode $node): ?WrapperNode {
        // If the node is a Document then the wrapperNode is this's wrapperNode
        // property.
        if ($node instanceof Document) {
            return $this->wrapperNode;
        }

        // If the wrapper node already exists then return that.
        if ($wrapperNode = $this->nodeCache->get($node)) {
            return $wrapperNode;
        }

        // If the node didn't exist we must construct the wrapper node's class name
        // based upon the node's class name
        if ($node instanceof \DOMAttr) {
            $className = 'Attr';
        } elseif ($node instanceof \DOMCdataSection) {
            $className = 'CDATASection';
        } elseif ($node instanceof \DOMComment) {
            $className = 'Comment';
        } elseif ($node instanceof \DOMDocumentFragment) {
            $className = 'DocumentFragment';
        } elseif ($node instanceof \DOMDocumentType) {
            $className = 'DocumentType';
        } elseif ($node instanceof \DOMElement) {
            $namespace = $node->namespaceURI;
            if ($namespace === null) {
                if ($node->nodeName === 'template') {
                    $className = 'HTMLTemplateElement';
                } else {
                    $className = 'HTMLElement';
                }
            } elseif ($namespace === WrapperNode::SVG_NAMESPACE) {
                $className = 'SVGElement';
            } elseif ($namespace === WrapperNode::MATHML_NAMESPACE) {
                $className = 'MathMLElement';
            } else {
                $className = 'Element';
            }
        } elseif ($node instanceof \DOMProcessingInstruction) {
            $className = 'ProcessingInstruction';
        } elseif ($node instanceof \DOMText) {
            $className = 'Text';
        }

        $wrapperNode = Reflection::createFromProtectedConstructor(self::$parentNamespace . "\\$className", $node);

        // We need to work around a PHP DOM bug where doctype nodes aren't associated
        // with a document until they're appended.
        if ($className === 'DocumentType') {
            Reflection::setProtectedProperties($wrapperNode, [ '_ownerDocument' => $this->_wrapperNode ]);
        }

        $this->nodeCache->set($wrapperNode, $node);
        return $wrapperNode;
    }
}
