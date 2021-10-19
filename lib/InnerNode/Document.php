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

    protected WrapperDocument $wrapperNode;
    protected NodeMap $nodeMap;


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

        $this->wrapperNode = $wrapperNode;
        $this->nodeMap = new NodeMap();
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
            case __NAMESPACE__ . '\\Attr': $className = "MensBeam\\HTML\\DOM\\Attr";
            break;
            case __NAMESPACE__ . '\\CDATASection': $className = "MensBeam\\HTML\\DOM\\CDATASection";
            break;
            case __NAMESPACE__ . '\\Comment': $className = "MensBeam\\HTML\\DOM\\Comment";
            break;
            case __NAMESPACE__ . '\\Document': $className = "MensBeam\\HTML\\DOM\\Document";
            break;
            case __NAMESPACE__ . '\\DocumentFragment': $className = "MensBeam\\HTML\\DOM\\DocumentFragment";
            break;
            case __NAMESPACE__ . '\\Element':
                if (($node->namespaceURI === null || $node->namespaceURI === Parser::HTML_NAMESPACE) && $node->nodeName === 'template') {
                    $className = "MensBeam\\HTML\\DOM\\HTMLTemplateElement";
                } else {
                    $className = "MensBeam\\HTML\\DOM\\Element";
                }
            break;
            case __NAMESPACE__ . '\\ProcessingInstruction': $className = "MensBeam\\HTML\\DOM\\ProcessingInstruction";
            break;
            case __NAMESPACE__ . '\\Text': $className = "MensBeam\\HTML\\DOM\\ProcessingInstruction";
            break;
        }

        // Nodes cannot be created from their constructors normally, so let's bypass all
        // that shit.
        $reflector = new \ReflectionClass($className);
        $wrapper = $reflector->newInstanceWithoutConstructor();
        $constructor = new \ReflectionMethod($wrapper, '__construct');
        $constructor->setAccessible(true);
        $constructor->invoke($wrapper, $node);
        $this->nodeMap->set($wrapper, $node);

        return $wrapper;
    }
}
