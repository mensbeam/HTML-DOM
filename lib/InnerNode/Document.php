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

        parent::registerNodeClass('DOMAttr', Attr::class);
        parent::registerNodeClass('DOMComment', Comment::class);
        parent::registerNodeClass('DOMCDATASection', CDATASection::class);
        parent::registerNodeClass('DOMDocument', self::class);
        parent::registerNodeClass('DOMDocumentFragment', DocumentFragment::class);
        parent::registerNodeClass('DOMElement', Element::class);
        parent::registerNodeClass('DOMProcessingInstruction', ProcessingInstruction::class);
        parent::registerNodeClass('DOMText', Text::class);

        $this->nodeMap = new NodeMap();
        // Use a weak reference here to prevent a circular reference
        $this->_wrapperNode = \WeakReference::create($wrapperNode);

        if (self::$parentNamespace === null) {
            self::$parentNamespace = substr(__NAMESPACE__, 0, strrpos(__NAMESPACE__, '\\'));
        }
    }


    public function getWrapperNode(?\DOMNode $node = null): WrapperNode {
        // If the node is a Document then the wrapperNode is this's wrapperNode
        // property.
        if ($node instanceof Document || $node === null) {
            return $this->wrapperNode;
        }

        // If the wrapper node already exists then return that.
        if ($wrapperNode = $this->nodeMap->get($node)) {
            return $wrapperNode;
        }

        // If the node didn't exist we must construct the wrapper node's class name
        // based upon the node's class name
        $className = $node::class;
        switch ($className) {
            case __NAMESPACE__ . '\\Attr': $className = self::$parentNamespace . "\\Attr";
            break;
            case __NAMESPACE__ . '\\CDATASection': $className = self::$parentNamespace . "\\CDATASection";
            break;
            case __NAMESPACE__ . '\\Comment': $className = self::$parentNamespace . "\\Comment";
            break;
            case __NAMESPACE__ . '\\Document': $className = self::$parentNamespace . "\\Document";
            break;
            case __NAMESPACE__ . '\\DocumentFragment': $className = self::$parentNamespace . "\\DocumentFragment";
            break;
            case 'DOMDocumentType': $className = self::$parentNamespace . "\\DocumentType";
            break;
            case __NAMESPACE__ . '\\Element':
                if (($node->namespaceURI === null || $node->namespaceURI === Parser::HTML_NAMESPACE) && $node->nodeName === 'template') {
                    $className = self::$parentNamespace . "\\HTMLTemplateElement";
                } else {
                    $className = self::$parentNamespace . "\\Element";
                }
            break;
            case __NAMESPACE__ . '\\ProcessingInstruction': $className = self::$parentNamespace . "\\ProcessingInstruction";
            break;
            case __NAMESPACE__ . '\\Text': $className = self::$parentNamespace . "\\Text";
            break;
            case __NAMESPACE__ . '\\XMLDocument': $className = self::$parentNamespace . "\\XMLDocument";
            break;
        }

        // Nodes cannot be created from their constructors normally
        return Factory::createFromProtectedConstructor($className, $node);
    }
}
