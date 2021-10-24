<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\InnerNode;
use MensBeam\Framework\MagicProperties;
use MensBeam\HTML\DOM\{
    Document as WrapperDocument,
    Node as WrapperNode
};
use MensBeam\HTML\Parser;


class Document extends \DOMDocument {
    use MagicProperties;

    protected NodeMap $nodeMap;
    protected \WeakReference $_wrapperNode;

    protected function __get_wrapperNode(): WrapperNode {
        return $this->_wrapperNode->get();
    }

    private static ?string $parentNamespace = null;


    public function __construct(WrapperDocument $wrapperNode) {
        parent::__construct();
        parent::registerNodeClass('DOMDocument', self::class);

        $this->nodeMap = new NodeMap();
        // Use a weak reference here to prevent a circular reference
        $this->_wrapperNode = \WeakReference::create($wrapperNode);

        if (self::$parentNamespace === null) {
            self::$parentNamespace = substr(__NAMESPACE__, 0, strrpos(__NAMESPACE__, '\\'));
        }
    }


    public function getWrapperNode(?\DOMNode $node = null): ?WrapperNode {
        if ($node === null) {
            return null;
        }
        // If the node is a Document then the wrapperNode is this's wrapperNode
        // property.
        if ($node instanceof Document) {
            return $this->wrapperNode;
        }

        // If the wrapper node already exists then return that.
        if ($wrapperNode = $this->nodeMap->get($node)) {
            return $wrapperNode;
        }

        // If the node didn't exist we must construct the wrapper node's class name
        // based upon the node's class name
        if ($node instanceof \DOMAttr) {
            $className = 'Attr';
        } elseif ($node instanceof \DOMCDATASection) {
            $className = 'CDATASection';
        } elseif ($node instanceof \DOMComment) {
            $className = 'Comment';
        } elseif ($node instanceof \DOMDocument) {
            $className = 'Document';
        } elseif ($node instanceof \DOMDocumentFragment) {
            $className = 'DocumentFragment';
        } elseif ($node instanceof \DOMDocumentType) {
            $className = 'DOMDocumentType';
        } elseif ($node instanceof \DOMElement) {
            $namespace = $node->namespaceURI;
            if ($namespace === null) {
                if ($node->nodeName === 'template') {
                    $className = 'HTMLTemplateElement';
                } else {
                    $className = 'HTMLElement';
                }
            } elseif ($namespace === Parser::SVG_NAMESPACE) {
                $className = 'SVGElement';
            } elseif ($namespace === Parser::MATHML_NAMESPACE) {
                $className = 'MathMLElement';
            } else {
                $className = 'Element';
            }
        } elseif ($node instanceof \DOMProcessingInstruction) {
            $className = 'ProcessingInstruction';
        } elseif ($node instanceof \DOMText) {
            $className = 'Text';
        } elseif ($node instanceof XMLDocument) {
            $className = 'XMLDocument';
        }

        // If the class is to be a CDATASection, DocumentFragment, or Text then the
        // object needs to be created differently because they have public constructors,
        // unlike other nodes.
        if ($className === 'CDATASection' || $className === 'DocumentFragment' || $className === 'Text') {
            $reflector = new \ReflectionClass(self::$parentNamespace . "\\$className");
            $wrapperNode = $reflector->newInstanceWithoutConstructor();
            $property = new \ReflectionProperty($wrapperNode, 'innerNode');
            $property->setAccessible(true);
            $property->setValue($wrapperNode, $node);
            return $wrapperNode;
        } else {
            $wrapperNode = Reflection::createFromProtectedConstructor(self::$parentNamespace . "\\$className", $node);
        }

        $this->nodeMap->set($wrapperNode, $this);
        return $wrapperNode;
    }
}
