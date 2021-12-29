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
    public const POTENTIAL_CUSTOM_ELEMENT_NAME_REGEX = '/^[a-z][a-z0-9-\._\x{B7}\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{203F}-\x{2040}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}]*-[a-z0-9-\._\x{B7}\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{203F}-\x{2040}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}]*$/Su';
    public const QNAME_PRODUCTION_REGEX = '/^([A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}][A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}-\.0-9\x{B7}\x{0300}-\x{036F}\x{203F}-\x{2040}]*:)?[A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}][A-Z_a-z\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{2FF}\x{370}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}-\.0-9\x{B7}\x{0300}-\x{036F}\x{203F}-\x{2040}]*$/Su';

    protected NodeCache $nodeCache;
    protected \WeakReference $_wrapperNode;
    protected ?\DOMXPath $_xpath = null;

    protected function __get_wrapperNode(): WrapperNode {
        return $this->_wrapperNode->get();
    }

    protected function __get_xpath(): \DOMXPath {
        if ($this->_xpath === null) {
            $this->_xpath = new \DOMXPath($this);
            $this->_xpath->registerNamespace('xmlns', WrapperNode::XMLNS_NAMESPACE);
        }

        return $this->_xpath;
    }

    private static ?string $parentNamespace = null;


    public function __construct(WrapperDocument $wrapperNode) {
        parent::__construct();
        parent::registerNodeClass('DOMDocument', self::class);

        $this->nodeCache = new NodeCache();
        // Use a weak reference here to prevent a circular reference
        $this->_wrapperNode = \WeakReference::create($wrapperNode);

        if (self::$parentNamespace === null) {
            // This line is covered, but pcov declares it not covered for some reason...
            self::$parentNamespace = substr(__NAMESPACE__, 0, strrpos(__NAMESPACE__, '\\')); // @codeCoverageIgnore
        }
    }


    public function getWrapperNode(\DOMNode $node): ?WrapperNode {
        // If the node is this document then return the wrapper node; it's already
        // known.
        if ($node === $this) {
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
            if ($namespace === null && !$this->wrapperNode instanceof WrapperXMLDocument) {
                # The element interface for an element with name name in the HTML namespace is
                # determined as follows:

                $name = $node->nodeName;

                # If name is applet, bgsound, blink, isindex, keygen, multicol, nextid, or
                # spacer, then return HTMLUnknownElement.
                if (in_array($name, [ 'applet', 'bgsound', 'blink', 'isindex', 'keygen', 'multicol', 'nextid', 'spacer' ])) {
                    $className = 'HTMLUnknownElement';
                }
                # If name is acronym, basefont, big, center, nobr, noembed, noframes, plaintext, rb, rtc, strike, or tt, then return HTMLElement.
                elseif (in_array($name, [ 'acronym', 'basefont', 'big', 'center', 'nobr', 'noembed', 'noframes', 'plaintext', 'rb', 'rtc', 'strike', 'tt' ])) {
                    $className = 'HTMLElement';
                }
                # If name is listing or xmp, then return HTMLPreElement.
                elseif (in_array($name, [ 'listing', 'xmp' ])) {
                    $className = 'HTMLPreElement';
                }
                # Otherwise, if this specification defines an interface appropriate for the element type corresponding to the local name name, then return that interface.
                # If other applicable specifications define an appropriate interface for name, then return the interface they define.
                elseif ($name === 'pre') {
                    $className = 'HTMLPreElement';
                } elseif ($name === 'template') {
                    $className = 'HTMLTemplateElement';
                }
                // This is done until we do element classes
                elseif (in_array($name, [ 'a', 'abbr', 'address', 'area', 'article', 'aside', 'audio', 'b', 'base', 'bdi', 'bdo', 'blockquote', 'body', 'br', 'button', 'canvas', 'caption', 'cite', 'code', 'col', 'colgroup', 'content', 'data', 'datalist', 'dd', 'del', 'details', 'dfn', 'dialog', 'dir', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'figcaption', 'figure', 'font', 'footer', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'header', 'hgroup', 'hr', 'html', 'i', 'iframe', 'img', 'img', 'input', 'ins', 'kbd', 'label', 'legend', 'li', 'link', 'main', 'map', 'mark', 'marquee', 'math', 'menu', 'menuitem', 'meta', 'meter', 'nav', 'noscript', 'object', 'ol', 'optgroup', 'option', 'output', 'p', 'param', 'picture', 'portal', 'pre', 'progress', 'q', 'rp', 'rt', 'ruby', 's', 'samp', 'script', 'section', 'select', 'shadow', 'slot', 'small', 'source', 'span', 'strong', 'style', 'sub', 'summary', 'sup', 'svg', 'table', 'tbody', 'td', 'template', 'textarea', 'tfoot', 'th', 'thead', 'time', 'title', 'tr', 'track', 'u', 'ul', 'var', 'video', 'wbr' ])) {
                    $className = 'HTMLElement';
                }
                # If name is a valid custom element name, then return HTMLElement.
                # A valid custom element name is a sequence of characters name that meets all of
                # the following requirements:
                # • name must match the PotentialCustomElementName production:
                # • name must not be any of the following:
                #      • annotation-xml
                #      • color-profile
                #      • font-face
                #      • font-face-src
                #      • font-face-uri
                #      • font-face-format
                #      • font-face-name
                #      • missing-glyph
                elseif (preg_match(self::POTENTIAL_CUSTOM_ELEMENT_NAME_REGEX, $name) && !in_array($name, [ 'annotation-xml', 'color-profile', 'font-face', 'font-face-src', 'font-face-uri', 'font-face-format', 'font-face-name', 'missing-glyph' ])) {
                    $className = 'HTMLElement';
                }
                # Return HTMLUnknownElement.
                else {
                    $className = 'HTMLUnknownElement';
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
